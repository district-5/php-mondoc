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

namespace District5Tests\MondocTests\FunctionalityParts;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\MondocTypes;
use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class ModelQueryTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class ModelQueryTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function setUp(): void
    {
        MyService::deleteMulti([]);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::deleteMulti([]);
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testGetByIdWithInvalidIdReturnsNull()
    {
        $this->assertNull(MyService::getById('foo'));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testGetByIdsWithEmptyOrInvalidIdsReturnsEmptyArray()
    {
        $result = MyService::getByIds([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        $result = MyService::getByIds(['foo']);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testGetWhereKeyEqualsAndDoesNotEqual()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());
        $this->assertTrue($m->save());

        $this->assertCount(1, MyService::getMultiWhereKeyEqualsValue('age', 2));
        $this->assertCount(0, MyService::getMultiWhereKeyDoesNotEqualValue('age', 2));

        $this->assertEquals($m->getObjectIdString(), MyService::getOneWhereKeyEqualsValue('age', 2)->getObjectIdString());
        $this->assertNull(MyService::getOneWhereKeyDoesNotEqualValue('age', 2));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testExistsByIdAndByCriteria()
    {
        $m = new MyModel();
        $m->setAge(12345);
        $m->setName('existence');
        $this->assertTrue($m->save());

        $this->assertTrue(MyService::existsById($m->getObjectId()));
        $this->assertFalse(MyService::existsById(MondocTypes::newObjectId()));

        $this->assertTrue(MyService::exists(['age' => 12345]));
        $this->assertTrue(MyService::exists(['name' => 'existence']));
        $this->assertTrue(MyService::exists(['age' => 12345], ['sort' => ['age' => 1]]));
        $this->assertTrue(MyService::exists(['name' => 'existence'], ['limit' => 1]));

        $this->assertFalse(MyService::exists(['age' => 9876]));
        $this->assertFalse(MyService::exists(['name' => 'nope']));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testExistsWithQueryBuilder()
    {
        $m = new MyModel();
        $m->setAge(12345);
        $m->setName('existence');
        $this->assertTrue($m->save());

        $correctAge = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->integer('age', 12345));
        $correctName = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->string('name', 'existence'));
        $incorrectAge = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->integer('age', 9876));
        $incorrectName = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->string('name', 'nope'));

        $this->assertTrue(MyService::existsWithQueryBuilder($correctAge));
        $this->assertTrue(MyService::existsWithQueryBuilder($correctName));
        $this->assertFalse(MyService::existsWithQueryBuilder($incorrectAge));
        $this->assertFalse(MyService::existsWithQueryBuilder($incorrectName));
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testCountFindOneAndFindMany()
    {
        $name = 'Joe ' . $this->getUniqueKey();
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($name);
        $this->assertTrue($m->save());

        $theId = $m->getObjectIdString();

        $this->assertEquals(1, MyService::countAll(['name' => $name]));
        $builder = MyService::getQueryBuilder();
        $builder->addQueryPart(ValueEqualTo::get()->string('name', $name));
        $this->assertEquals(1, MyService::countAllByQueryBuilder($builder));

        $found = MyService::getOneByCriteria(['name' => $name]);
        /* @var $found MyModel */
        $this->assertNotNull($found);
        $this->assertInstanceOf(MyModel::class, $found);
        $this->assertEquals($name, $found->getName());
        $this->assertTrue($found->hasObjectId());
        $this->assertEquals($theId, $found->getObjectIdString());

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);
        $this->assertInstanceOf(MyModel::class, $multi[0]);
        $this->assertEquals($name, $multi[0]->getName());
        $this->assertTrue($multi[0]->hasObjectId());
        $this->assertEquals($theId, $multi[0]->getObjectIdString());

        $multiByIds = MyService::getByIds([$theId]);
        $this->assertCount(1, $multiByIds);
        $this->assertInstanceOf(MyModel::class, $multiByIds[0]);
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testUpdateAndRequery()
    {
        $name = 'Joe ' . $this->getUniqueKey();
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($name);
        $this->assertTrue($m->save());

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);

        $newName = 'joe-bloggs ' . microtime(false);
        $multi[0]->setName($newName);
        $this->assertTrue($multi[0]->save());

        $this->assertCount(1, MyService::getMultiByCriteria(['name' => $newName]));
        $this->assertCount(0, MyService::getMultiByCriteria(['name' => $name]));
    }
}
