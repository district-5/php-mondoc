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
use District5\Mondoc\Exception\MondocEncryptionException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\MondocConfig;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyDuplicateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\NoServiceModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class CloneTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class CloneTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocEncryptionException
     */
    public function testCloneModelWithSave()
    {
        $m = new MyModel();
        $m->setAge(102);
        $m->setName('Joe');
        $this->assertTrue($m->save());

        $clone = $m->clone(true);
        $this->assertNotEquals($m->getObjectIdString(), $clone->getObjectIdString());
        $this->assertEquals($m->getAge(), $clone->getAge());
        $this->assertEquals($m->getName(), $clone->getName());

        $this->assertTrue($m->delete());
        $this->assertTrue($clone->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     */
    public function testCloneModelWithAlternateModel()
    {
        $m = new MyModel();
        $m->setAge(102);
        $m->setName('Joe');
        $this->assertTrue($m->save());

        MondocConfig::getInstance()->addServiceMapping(
            MyDuplicateModel::class,
            MyService::class
        ); // required to map a service to a model

        $clone = $m->clone(true, MyDuplicateModel::class);
        $this->assertInstanceOf(MyDuplicateModel::class, $clone);
        $this->assertNotEquals($m->getObjectIdString(), $clone->getObjectIdString());
        $this->assertEquals($m->getAge(), $clone->getAge());
        $this->assertEquals($m->getName(), $clone->getName());

        $clone2 = $m->clone(true, new MyDuplicateModel());
        $this->assertInstanceOf(MyDuplicateModel::class, $clone2);
        $this->assertNotEquals($m->getObjectIdString(), $clone2->getObjectIdString());
        $this->assertEquals($m->getAge(), $clone2->getAge());
        $this->assertEquals($m->getName(), $clone2->getName());

        $this->assertTrue($m->delete());
        $this->assertTrue($clone->delete());
        $this->assertTrue($clone2->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocEncryptionException
     */
    public function testCloneModelWithoutSave()
    {
        $m = new MyModel();
        $m->setAge(102);
        $m->setName('Joe');
        $this->assertTrue($m->save());

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $clone = $m->clone(false); // false means do not save
        $this->assertFalse($clone->hasObjectId());
        $this->assertFalse($clone->hasPresetObjectId());

        $this->assertEquals($m->getAge(), $clone->getAge());
        $this->assertEquals($m->getName(), $clone->getName());

        $this->assertTrue($m->delete()); // the clone has not been saved
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocEncryptionException
     */
    public function testCloneModelWithInvalidModel()
    {
        $this->expectException(MondocServiceMapErrorException::class);
        $m = new MyModel();
        $m->setAge(102);
        $m->setName('Joe');
        $this->assertTrue($m->save());

        $this->assertNull($m->clone(true, NoServiceModel::class));
    }
}
