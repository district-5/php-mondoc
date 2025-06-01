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

namespace District5Tests\MondocTests;

use DateTime;
use District5\Date\Date;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\HelperTraitsModel;
use District5Tests\MondocTests\TestObjects\Model\HelperTraitsOtherModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\HelperTraitsOtherService;
use District5Tests\MondocTests\TestObjects\Service\HelperTraitsService;
use MongoDB\BSON\UTCDateTime;

/**
 * Class HelperTraitsModelTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class HelperTraitsModelTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocConfigConfigurationException
     */
    public function testGetCollection()
    {
        $collection = HelperTraitsService::getCollection(HelperTraitsService::class);
        $otherCollection = HelperTraitsService::getCollection();
        $this->assertEquals(
            $collection->getCollectionName(),
            $otherCollection->getCollectionName()
        );
    }

    public function testIsOrIsNotVersionable()
    {
        $m = new DateModel();
        $this->assertFalse($m->isVersionableModel());

        $m = new HelperTraitsModel();
        $this->assertNull($m->getModifiedDate());
        $this->assertNull($m->getCreatedDate());

        $m->touchModifiedDate();
        $m->touchCreatedDate();

        $this->assertInstanceOf(DateTime::class, $m->getModifiedDate());
        $this->assertInstanceOf(DateTime::class, $m->getCreatedDate());

        $this->assertTrue($m->isVersionableModel());
    }

    /** @noinspection PhpRedundantOptionalArgumentInspection */
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testFullVersioningFunctionality()
    {
        $m = new HelperTraitsModel();
        $m->setName('Foo');
        $this->assertEquals(0, $m->getRevisionNumber());
        $this->assertTrue($m->save());
        $this->assertEquals(1, $m->getModelVersion());
        $this->assertEquals(1, $m->getRevisionNumber());
        $this->assertTrue($m->hasObjectId());

        $model = HelperTraitsService::getById($m->getObjectId());
        /* @var $model HelperTraitsModel */
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertEquals(1, $model->getRevisionNumber());
        $this->assertEquals('Foo', $model->getName());
        $model->incrementModelVersion();
        $this->assertEquals(2, $model->getModelVersion());
        $this->assertEquals(1, $m->getRevisionNumber());
        $this->assertTrue($model->save());
        $this->assertEquals(2, $model->getRevisionNumber()); // triggers increment on save only

        $model = HelperTraitsService::getById($m->getObjectId());
        $this->assertInstanceOf(DateTime::class, $model->getCreatedDate(false));
        $this->assertInstanceOf(UTCDateTime::class, $model->getCreatedDate(true));
        $this->assertTrue(Date::validateObject($model->getCreatedDate(false))->isOlderThan(Date::modify(Date::nowUtc())->add()->seconds(5)));
        $this->assertTrue(Date::validateObject($model->getCreatedDate(false))->isNewerThan(Date::modify(Date::nowUtc())->minus()->seconds(5)));

        $this->assertInstanceOf(DateTime::class, $model->getModifiedDate(false));
        $this->assertInstanceOf(UTCDateTime::class, $model->getModifiedDate(true));
        $this->assertTrue(Date::validateObject($model->getModifiedDate(false))->isOlderThan(Date::modify(Date::nowUtc())->add()->seconds(5)));
        $this->assertTrue(Date::validateObject($model->getModifiedDate(false))->isNewerThan(Date::modify(Date::nowUtc())->minus()->seconds(5)));

        /* @var $model HelperTraitsModel */
        $this->assertEquals(2, $model->getModelVersion());
        $this->assertEquals(2, $model->getRevisionNumber());
        $this->assertEquals('Foo', $model->getName());
        $model->decrementModelVersion();
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertTrue($model->save());

        $model = HelperTraitsService::getById($m->getObjectId());
        /* @var $model HelperTraitsModel */
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertEquals(3, $model->getRevisionNumber());
        $this->assertEquals('Foo', $model->getName());

        $model->setName('Bar');
        $model->setRevisionNumber(PHP_INT_MAX); // integer overflow test
        $model->save(); // this won't increment on save, as it's already been set as dirty.

        $newModel = HelperTraitsService::getById($m->getObjectId());
        /* @var $newModel HelperTraitsModel */
        // PHP_INT_MAX is 9223372036854775807
        $this->assertEquals(PHP_INT_MAX, $newModel->getRevisionNumber());
        $newModel->incrementRevisionNumber();
        $this->assertEquals(1, $newModel->getRevisionNumber());

        $this->assertTrue($newModel->delete());
    }

    public function testIsXModelTrait()
    {
        $myModel = new MyModel();
        $this->assertFalse($myModel->isVersionableModel());
        $this->assertFalse($myModel->isRevisionNumberModel());

        $traitModel = new HelperTraitsModel();
        $this->assertTrue($traitModel->isVersionableModel());
        $this->assertTrue($traitModel->isRevisionNumberModel());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testInsertMultiOnModelsWithTraits()
    {
        $model = new HelperTraitsModel();
        $model->setName('Foo');

        $other = new HelperTraitsOtherModel();
        $other->setName('Bar');

        $this->assertTrue(HelperTraitsService::insertMulti([$model, $other]));

        $this->assertTrue($model->hasObjectId());
        $this->assertTrue($other->hasObjectId());

        $this->assertInstanceOf(DateTime::class, $model->getCreatedDate());
        $this->assertInstanceOf(DateTime::class, $model->getModifiedDate());
        $this->assertInstanceOf(DateTime::class, $other->getCreatedDate());
        $this->assertInstanceOf(DateTime::class, $other->getModifiedDate());
        $this->assertEquals(1, $model->getRevisionNumber());
        $this->assertEquals(1, $other->getRevisionNumber());

        HelperTraitsService::insertMulti([$model, $other]);
        $model->delete();
        $other->delete();
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        HelperTraitsService::deleteMulti([]);
        HelperTraitsOtherService::deleteMulti([]);
        parent::tearDown();
    }
}
