<?php
/**
 * District5 Mondoc Library
 *
 * @author      District5 <hello@district5.co.uk>
 * @copyright   District5 <hello@district5.co.uk>
 * @link        https://www.district5.co.uk
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MondocTests;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocEncryptionException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class BuilderTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class BuilderTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     */
    public function testPersistAndQuery()
    {
        MyService::deleteMulti([]);
        $objectId = new ObjectId();

        $m = new MyModel();
        $m->setName('Foo');
        $m->setAge(1);
        $m->setPresetObjectId($objectId);
        $this->assertTrue($m->save());
        $this->assertEquals($objectId->__toString(), $m->getObjectIdString());

        $queryBuilderStringAndInt = new QueryBuilder();
        $equalsStringInt = new ValueEqualTo();
        $equalsStringInt->string('name', 'Foo');
        $equalsStringInt->integer('age', 1);
        $queryBuilderStringAndInt->addQueryPart($equalsStringInt);

        $queryBuilderObjectId = new QueryBuilder();
        $equalsObjectId = new ValueEqualTo();
        $equalsObjectId->mongoNative('_id', $objectId);
        $queryBuilderObjectId->addQueryPart($equalsObjectId);

        $getModel = MyService::getOneByQueryBuilder($queryBuilderStringAndInt);
        /* @var $getModel MyModel */
        $this->assertEquals($m->getObjectIdString(), $getModel->getObjectIdString());
        $this->assertEquals('Foo', $getModel->getName());
        $this->assertEquals(1, $getModel->getAge());

        $getModel = MyService::getOneByQueryBuilder($queryBuilderObjectId);
        /* @var $getModel MyModel */
        $this->assertEquals($m->getObjectIdString(), $getModel->getObjectIdString());
        $this->assertEquals('Foo', $getModel->getName());
        $this->assertEquals(1, $getModel->getAge());

        $getModels = MyService::getMultiByQueryBuilder($queryBuilderStringAndInt);
        /* @var $getModels MyModel[] */
        $this->assertCount(1, $getModels);
        $this->assertEquals($m->getObjectIdString(), $getModels[0]->getObjectIdString());
        $this->assertEquals('Foo', $getModels[0]->getName());
        $this->assertEquals(1, $getModels[0]->getAge());

        $getModels = MyService::getMultiByQueryBuilder($queryBuilderObjectId);
        /* @var $getModels MyModel[] */
        $this->assertCount(1, $getModels);
        $this->assertEquals($m->getObjectIdString(), $getModels[0]->getObjectIdString());
        $this->assertEquals('Foo', $getModels[0]->getName());
        $this->assertEquals(1, $getModels[0]->getAge());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::deleteMulti([]);
    }
}
