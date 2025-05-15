<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

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

namespace District5Tests\MondocTests;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocEncryptionException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\ModelChanges\FooModel;
use District5Tests\MondocTests\TestObjects\ModelChanges\FooService;
use District5Tests\MondocTests\TestObjects\ModelChanges\FooWithMoreFieldsModel;
use District5Tests\MondocTests\TestObjects\ModelChanges\FooWithMoreFieldsService;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class TestNewFieldIntroductionTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class TestNewFieldIntroductionTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocEncryptionException
     */
    public function testBasicModel()
    {
        $m = new FooModel();
        $m->setName('foo');
        $this->assertTrue($m->save());

        $retrieved = FooService::getById($m->getObjectId());
        $this->assertEquals($retrieved->getObjectIdString(), $m->getObjectIdString());
        $this->assertEquals($retrieved->getName(), $m->getName());

        $this->assertTrue($m->delete());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocEncryptionException
     */
    public function testOtherBasicModelWithSameCollection()
    {
        $m = new FooWithMoreFieldsModel();
        $m->setName('foo');
        $m->setAge(39);
        $m->setData(['foo' => 'bar']);
        $this->assertTrue($m->save());

        $retrieved = FooWithMoreFieldsService::getById($m->getObjectId());
        $this->assertEquals($retrieved->getObjectIdString(), $m->getObjectIdString());
        $this->assertEquals($retrieved->getName(), $m->getName());
        $this->assertEquals($retrieved->getAge(), $m->getAge());
        $this->assertEquals($retrieved->getData(), $m->getData());

        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocEncryptionException
     * @throws MondocConfigConfigurationException
     */
    public function testSavingWithFooThenIntroducingArray()
    {
        $m = new FooModel();
        $m->setName('foo');
        $this->assertTrue($m->save());

        $retrieved = FooWithMoreFieldsService::getById($m->getObjectId()); // different model
        $this->assertEquals($retrieved->getObjectIdString(), $m->getObjectIdString());

        $retrieved->setData(['foo' => 'bar']);
        $retrieved->setAge(40);
        $this->assertTrue($retrieved->save());

        $retrieved = FooWithMoreFieldsService::getById($m->getObjectId());
        $this->assertEquals('bar', $retrieved->getOriginalBsonDocument()->data->foo);
        $this->assertEquals('foo', $retrieved->getOriginalBsonDocument()->name);
        $this->assertEquals(40, $retrieved->getOriginalBsonDocument()->age);
        $this->assertEquals(40, $retrieved->getAge());
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
