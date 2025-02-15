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

namespace District5Tests\MondocTests\FunctionalityParts;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\NoServiceModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class RemoveKeysAndAtomicTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class RemoveKeysAndAtomicTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testRemovingKey()
    {
        $m = new MyModel();
        $m->setAge(101);
        $m->setName($this->getUniqueKey());
        $m->save();

        $this->assertEquals(101, MyService::getById($m->getObjectId())->getAge());
        $m->addUnmappedKey('age', 101); // hack to get it into unmapped
        $this->assertTrue(MyService::removeField('age', $m));
        $this->assertFalse(MyService::removeField('noSuchField', $m));
        $this->assertEquals(0, MyService::getById($m->getObjectId())->getAge()); // 0 is the default value
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testIncrementInvalidModel()
    {
        $this->expectException(MondocServiceMapErrorException::class);
        $m = new NoServiceModel();
        $this->assertFalse($m->inc('age', 2));
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testDecrementInvalidModel()
    {
        $this->expectException(MondocServiceMapErrorException::class);
        $m = new NoServiceModel();
        $this->assertFalse($m->dec('age', 2));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testIncrementDecrementModelShortcuts()
    {
        $m = new MyModel();
        $m->setAge(90);
        $m->incrementAge();
        $m->decrementAge();
        $m->setName($this->getUniqueKey());
        $m->save();

        $this->assertEquals(90, MyService::getById($m->getObjectId())->getAge());

        $this->assertTrue($m->incrementAge());
        $this->assertEquals(91, $m->getAge());
        $this->assertEquals(91, MyService::getById($m->getObjectId())->getAge());

        $this->assertTrue($m->decrementAge());
        $this->assertTrue($m->decrementAge());
        $this->assertEquals(89, $m->getAge());
        $this->assertEquals(89, MyService::getById($m->getObjectId())->getAge());

        // delete the model
        $this->assertTrue($m->delete());

        $m = new MyModel();
        $m->setAge(90);
        $m->setName('foobar');
        $m->save();
        $this->assertEquals(90, MyService::getById($m->getObjectId())->getAge());
        MyService::inc($m->getObjectId(), 'age', 2);
        $this->assertEquals(92, MyService::getById($m->getObjectId())->getAge());
        MyService::dec($m->getObjectId(), 'age', 4);
        $this->assertEquals(88, MyService::getById($m->getObjectId())->getAge());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testIncrementDecrementMulti()
    {
        $m = new MyModel();
        $m->setAge(101);
        $m->setName($this->getUniqueKey());
        $m->save();

        MyService::decMulti($m->getObjectId(), ['age' => 2]);
        $this->assertEquals(99, MyService::getById($m->getObjectId())->getAge());
        MyService::incMulti($m->getObjectId(), ['age' => 4]);
        $this->assertEquals(103, MyService::getById($m->getObjectId())->getAge());

        // delete the model
        $this->assertTrue($m->delete());
    }
}
