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
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class DeletionTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class DeletionTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testDeleteByIdsWhenNoIds()
    {
        $this->assertEquals(0, MyService::deleteByIds([]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testDeleteMultiWithoutIds()
    {
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid());
        $m2 = new MyModel();
        $m2->setAge(2)->setName(uniqid());

        $this->assertEquals(0, MyService::deleteModels([$m, $m2]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteByIdsHasResults()
    {
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();
        $mT = new MyModel();
        $mT->setAge(2)->setName(uniqid())->save();

        $this->assertEquals(2, MyService::deleteByIds([$m->getObjectId(), $mT->getObjectId()]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteMultipleModelsResultsInSuccess()
    {
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();
        $mT = new MyModel();
        $mT->setAge(2)->setName(uniqid())->save();

        $this->assertEquals(2, MyService::deleteModels([$m, $mT]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteByIdsHasSingleResult()
    {
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();

        $this->assertEquals(1, MyService::deleteByIds([$m->getObjectId(), new ObjectId()]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteModel()
    {
        MyService::deleteMulti([]);
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();

        $this->assertTrue(MyService::deleteModel($m));

        $another = new MyModel();
        $another->setAge(1)->setName(uniqid());
        $fakeId = new ObjectId();
        $another->setObjectId($fakeId);
        $this->assertFalse(MyService::deleteModel($another));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteMultiPassesBothScenarios()
    {
        MyService::deleteMulti([]);
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();
        $mT = new MyModel();
        $mT->setAge(1)->setName(uniqid())->save();

        $this->assertEquals(0, MyService::deleteMulti(['age' => 2]));
        $this->assertEquals(2, MyService::deleteMulti(['age' => 1]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteOne()
    {
        MyService::deleteMulti([]);
        $m = new MyModel();
        $m->setAge(1)->setName(uniqid())->save();
        $mT = new MyModel();
        $mT->setAge(2)->setName(uniqid())->save();

        $this->assertTrue(MyService::deleteOne(['age' => 1]));
        $this->assertFalse(MyService::deleteOne(['age' => 1]));
        $this->assertTrue(MyService::deleteOne(['age' => 2]));
        $this->assertFalse(MyService::deleteOne(['age' => 2]));
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::getCollection()->drop();
        parent::tearDown();
    }
}
