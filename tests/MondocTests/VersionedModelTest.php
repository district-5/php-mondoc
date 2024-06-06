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

use DateTime;
use District5\Date\Date;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\VersionedModel;
use District5Tests\MondocTests\TestObjects\Service\VersionedService;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * Class VersionedModelTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class VersionedModelTest extends MondocBaseTest
{
    public function testGetCollection()
    {
        $collection = VersionedService::getCollection(VersionedService::class);
        $otherCollection = VersionedService::getCollection();
        $this->assertEquals(
            $collection->getCollectionName(),
            $otherCollection->getCollectionName()
        );
    }

    public function testIsOrIsNotVersionable()
    {
        $m = new DateModel();
        $this->assertFalse($m->isVersionableModel());

        $m = new VersionedModel();
        $this->assertNull($m->getModifiedDate(false));
        $this->assertNull($m->getCreatedDate(false));

        $m->touchModifiedDate();
        $m->touchCreatedDate();

        $this->assertInstanceOf(DateTime::class, $m->getModifiedDate(false));
        $this->assertInstanceOf(DateTime::class, $m->getCreatedDate(false));

        $this->assertTrue($m->isVersionableModel());
    }

    /** @noinspection PhpRedundantOptionalArgumentInspection */
    public function testFullVersioningFunctionality()
    {

        $m = new VersionedModel();
        $m->setName('Foo');
        $this->assertTrue($m->save());
        $this->assertEquals(1, $m->getModelVersion());
        $this->assertTrue($m->hasObjectId());

        $model = VersionedService::getById($m->getObjectId());
        /* @var $model VersionedModel */
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertEquals('Foo', $model->getName());
        $model->incrementModelVersion();
        $this->assertEquals(2, $model->getModelVersion());
        $this->assertTrue($model->save());

        $model = VersionedService::getById($m->getObjectId());
        $this->assertInstanceOf(DateTime::class, $model->getCreatedDate(false));
        $this->assertInstanceOf(UTCDateTime::class, $model->getCreatedDate(true));
        $this->assertTrue(Date::validateObject($model->getCreatedDate(false))->isOlderThan(Date::modify(Date::nowUtc())->add()->seconds(5)));
        $this->assertTrue(Date::validateObject($model->getCreatedDate(false))->isNewerThan(Date::modify(Date::nowUtc())->minus()->seconds(5)));

        $this->assertInstanceOf(DateTime::class, $model->getModifiedDate(false));
        $this->assertInstanceOf(UTCDateTime::class, $model->getModifiedDate(true));
        $this->assertTrue(Date::validateObject($model->getModifiedDate(false))->isOlderThan(Date::modify(Date::nowUtc())->add()->seconds(5)));
        $this->assertTrue(Date::validateObject($model->getModifiedDate(false))->isNewerThan(Date::modify(Date::nowUtc())->minus()->seconds(5)));

        /* @var $model VersionedModel */
        $this->assertEquals(2, $model->getModelVersion());
        $this->assertEquals('Foo', $model->getName());
        $model->decrementModelVersion();
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertTrue($model->save());

        $model = VersionedService::getById($m->getObjectId());
        /* @var $model VersionedModel */
        $this->assertEquals(1, $model->getModelVersion());
        $this->assertEquals('Foo', $model->getName());
        $this->assertTrue($model->delete());
    }
}
