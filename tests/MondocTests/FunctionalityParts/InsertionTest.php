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

use DateTime;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class InsertionTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class InsertionTest extends MondocBaseTest
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMulti()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());

        $mT = new MyModel();
        $mT->setAge(2);
        $mT->setName(uniqid());

        $this->assertFalse($m->hasObjectId());
        $this->assertFalse($mT->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());
        $this->assertFalse($mT->hasPresetObjectId());

        $this->assertTrue(MyService::insertMulti([$m, $mT]));

        $this->assertTrue($m->hasObjectId());
        $this->assertTrue($mT->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());
        $this->assertFalse($mT->hasPresetObjectId());

        $this->assertTrue($m->delete());
        $this->assertTrue($mT->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testUpdateDocumentThatDoesntExist()
    {
        $m = new MyModel();
        $m->setObjectId(new ObjectId()); // it'd be OK if we used preset instead!
        $m->setAge(1);
        $a = uniqid();
        $m->setName($a);
        $this->assertFalse($m->save()); // this should fail as the document doesn't exist
        $m->unsetObjectId();
        $this->assertTrue($m->save()); // this should pass as the document doesn't contain an ID anymore exist
        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testDirtyFieldWithNewKeySet()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());
        $this->assertTrue($m->save());

        $model = MyService::getById($m->getObjectId());
        $model->proxyAddDirty('car');
        $model->proxySetKey('bestTv', 'A-Team');
        $model->save();

        $another = MyService::getById($m->getObjectId());
        $arrayCopy = $another->getOriginalBsonDocument()->getArrayCopy();
        $this->assertArrayHasKey('car', $arrayCopy);
        $this->assertArrayHasKey('bestTv', $arrayCopy);
        $this->assertNull($arrayCopy['car']); // invalid keys are not saved
        $this->assertEquals('A-Team', $arrayCopy['bestTv']);


        $another->setAge(2);
        $another->save();
        $arrayCopy = $another->getOriginalBsonDocument()->getArrayCopy();
        $this->assertArrayHasKey('car', $arrayCopy);
        $this->assertNull($arrayCopy['car']); // invalid keys are not saved

        $another->setAge(3);
        $another->proxyControlAsArray(true);
        $another->save();
        $anotherOne = MyService::getById($another->getObjectId());
        $bson = $anotherOne->getOriginalBsonDocument()->getArrayCopy();
        $this->assertArrayHasKey('foo', $bson);
        $this->assertEquals('bar', $bson['foo']);
        $this->assertArrayNotHasKey('car', $bson);

        $this->assertTrue($anotherOne->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMultiWithMultipleModelsAcrossTwoServices()
    {
        $mStart = new MyModel();
        $mStart->setAge(1);
        $mStart->setName(uniqid());

        $this->assertTrue(MyService::insertMulti([$mStart]));
        $this->assertTrue(MyService::deleteModel($mStart));

        $mTStart = new DateModel();
        $mTStart->setDate(DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-01 00:00:00'));

        $this->assertTrue(MyService::insertMulti([$mTStart]));
        $this->assertTrue(DateService::deleteModel($mTStart));


        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());

        $mT = new DateModel();
        $mT->setDate(DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-01 00:00:00'));

        $this->assertFalse($m->hasObjectId());
        $this->assertFalse($mT->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());
        $this->assertFalse($mT->hasPresetObjectId());

        $this->assertTrue(MyService::insertMulti([$m, $mT]));

        $this->assertTrue($m->hasObjectId());
        $this->assertTrue($mT->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());
        $this->assertFalse($mT->hasPresetObjectId());

        $this->assertEquals($m->getObjectIdString(), MyService::getOneByCriteria(['_id' => ['$eq' => $m->getObjectId()]])->getObjectIdString());
        $this->assertEquals($mT->getObjectIdString(), DateService::getOneByCriteria(['_id' => ['$eq' => $mT->getObjectId()]])->getObjectIdString());
        $this->assertEquals($mT->getObjectIdString(), DateService::getOneByCriteria(['_id' => ['$eq' => $mT->getObjectId()]], ['sort' => ['date' => -1]])->getObjectIdString());
        $this->assertNull(DateService::getOneByCriteria(['noKey' => 'noValue'], ['sort' => ['date' => -1]]));

        $this->assertTrue($m->delete());
        $this->assertTrue($mT->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMultiWithOneModel()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());

        $this->assertFalse($m->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());

        $this->assertTrue(MyService::insertMulti([$m]));

        $this->assertTrue($m->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());

        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMultiWithPresetIds()
    {
        $idOne = new ObjectId();
        $idTwo = new ObjectId();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());
        $m->setPresetObjectId($idOne);

        $mT = new MyModel();
        $mT->setAge(2);
        $mT->setName(uniqid());
        $mT->setPresetObjectId($idTwo);

        $this->assertFalse($m->hasObjectId());
        $this->assertFalse($mT->hasObjectId());
        $this->assertTrue($m->hasPresetObjectId());
        $this->assertTrue($mT->hasPresetObjectId());

        $this->assertTrue(MyService::insertMulti([$m, $mT]));

        $this->assertEquals($idOne->__toString(), $m->getObjectIdString());
        $this->assertEquals($idTwo->__toString(), $mT->getObjectIdString());

        $this->assertTrue($m->hasObjectId());
        $this->assertTrue($mT->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());
        $this->assertFalse($mT->hasPresetObjectId());

        $this->assertTrue($m->delete());
        $this->assertTrue($mT->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMultiWithOneModelWithPresetIds()
    {
        $idOne = new ObjectId();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());
        $m->setPresetObjectId($idOne);

        $this->assertFalse($m->hasObjectId());
        $this->assertTrue($m->hasPresetObjectId());

        $this->assertTrue(MyService::insertMulti([$m]));

        $this->assertEquals($idOne->__toString(), $m->getObjectIdString());

        $this->assertTrue($m->hasObjectId());
        $this->assertFalse($m->hasPresetObjectId());

        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testInsertMultiWithNoModels()
    {
        $this->assertTrue(MyService::insertMulti([]));
    }
}
