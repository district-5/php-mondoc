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

use District5\Date\Date;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Extensions\Retention\MondocRetentionModel;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;
use District5\Mondoc\Helper\MondocTypes;
use District5Tests\MondocTests\TestObjects\Model\SubLevelRetainedTestModel;
use District5Tests\MondocTests\TestObjects\Model\TopLevelRetainedTestModel;
use District5Tests\MondocTests\TestObjects\Service\SubLevelRetainedTestService;
use District5Tests\MondocTests\TestObjects\Service\TopLevelRetainedTestService;

/**
 * Class RetainedDataModelTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class RetainedDataModelTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testRawUnsavedRetentionModels()
    {
        $blankM = new TopLevelRetainedTestModel();
        $blankM->setObjectId(MondocTypes::newObjectId());
        $retentionModel = new MondocRetentionModel();
        $retentionModel->setSourceModel($blankM);
        $retentionModel->setRetentionData([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $retentionModel->setRetentionExpiry(
            Date::modify(Date::nowUtc())->plus()->days(1)
        );
        $this->assertFalse($retentionModel->hasRetentionExpired());
        $retentionModel->setRetentionExpiry(
            Date::modify(Date::nowUtc())->minus()->days(1)
        );
        $this->assertTrue($retentionModel->hasRetentionExpired());
        $retentionModel->setRetentionExpiry(null);
        $this->assertFalse($retentionModel->hasRetentionExpired());

        $this->assertEquals('bar', $retentionModel->getRetentionData()['foo']);
        $this->assertEquals('baz', $retentionModel->getRetentionData()['bar']);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testRetainedDataOnTopLevelModel()
    {
        $m = new TopLevelRetainedTestModel();
        $m->setName('foo');
        $m->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $m->setMondocRetentionExpiry(
            Date::modify(Date::nowUtc())->plus()->days(1)
        );
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());

        $savedId = $m->getObjectId();
        $retainedModel = MondocRetentionService::getLatestRetentionModelForModel(TopLevelRetainedTestModel::class, $savedId);
        $this->assertNotNull($retainedModel);
        $this->assertFalse($retainedModel->hasRetentionExpired());
        $this->assertEquals($m->getObjectIdString(), $retainedModel->getSourceObjectIdString());

        $this->assertInstanceOf(TopLevelRetainedTestModel::class, $retainedModel->toOriginalModel());
        $this->assertEquals('foo', $retainedModel->toOriginalModel()->getName());
        $this->assertEquals('bar', $retainedModel->getRetentionData()['foo']);
        $this->assertEquals('baz', $retainedModel->getRetentionData()['bar']);
        $this->assertTrue($m->delete());

        $again = MondocRetentionService::getLatestRetentionModelForModel(TopLevelRetainedTestModel::class, $savedId);
        $this->assertNotNull($again);
        $this->assertTrue($again->delete());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testRetainedDataOnExtendedModel()
    {
        $m = new SubLevelRetainedTestModel();
        $m->setName('foo');
        $m->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $ageSubModel = $m->getAge();
        $ageSubModel->setAge(42);
        $wordSubModel = $ageSubModel->getAgeWordModel();
        $wordSubModel->setWord('forty two');

        $m->setAge($ageSubModel);
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());

        $savedId = $m->getObjectId();
        $retainedModel = MondocRetentionService::getLatestRetentionModelForModel(SubLevelRetainedTestModel::class, $savedId);
        $this->assertNotNull($retainedModel);
        $this->assertEquals($m->getObjectIdString(), $retainedModel->getSourceObjectIdString());
        $this->assertInstanceOf(SubLevelRetainedTestModel::class, $retainedModel->toOriginalModel());
        $this->assertEquals('foo', $retainedModel->toOriginalModel()->getName());
        $this->assertEquals('bar', $retainedModel->getRetentionData()['foo']);
        $this->assertEquals('baz', $retainedModel->getRetentionData()['bar']);

        $ageSubModel = $retainedModel->toOriginalModel()->getAge();
        $this->assertEquals(42, $ageSubModel->getAge());
        $wordSubModel = $ageSubModel->getAgeWordModel();
        $this->assertEquals('forty two', $wordSubModel->getWord());
        $this->assertTrue($m->delete());

        $again = MondocRetentionService::getLatestRetentionModelForModel(SubLevelRetainedTestModel::class, $savedId);
        $this->assertNotNull($again);
        $this->assertTrue($again->delete());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testIterationsOfSavesMatchExpected()
    {
        $m = new TopLevelRetainedTestModel();
        $m->setName('foo');
        $m->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());

        $this->assertEquals(1, MondocRetentionService::countRetentionModelsForClassName(TopLevelRetainedTestModel::class));
        $m->setName('bar');
        $this->assertTrue($m->save());
        $this->assertEquals(2, MondocRetentionService::countRetentionModelsForClassName(TopLevelRetainedTestModel::class));

        $totalLoops = 10;
        for ($i = 1; $i < ($totalLoops + 1); $i++) {
            $m->setName('foo' . $i);
            $this->assertTrue($m->save());
            $this->assertEquals($i + 2, MondocRetentionService::countRetentionModelsForClassName(TopLevelRetainedTestModel::class));
        }

        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForClassName(TopLevelRetainedTestModel::class, 12, 1);
        $this->assertEquals(1, $paginator->getTotalPages());
        $paginatorFromModel = $m->getRetentionPaginatorForModel(12, 1);
        $this->assertEquals(1, $paginatorFromModel->getTotalPages());
        $this->assertGreaterThan(0, $paginatorFromModel->getTotalResults());
        $this->assertEquals($paginator->getTotalResults(), $paginatorFromModel->getTotalResults());

        $modelBased = new TopLevelRetainedTestModel();
        $paginatorFromModel = $modelBased->getRetentionPaginatorForModel(12, 1); // This paginator is based on the entire TopLevelRetainedTestModel class.
        $this->assertEquals(1, $paginatorFromModel->getTotalPages());
        $this->assertGreaterThan(0, $paginatorFromModel->getTotalResults());
        $this->assertEquals($paginator->getTotalResults(), $paginatorFromModel->getTotalResults());

        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForModel($m, 12, 1);
        $this->assertEquals(1, $paginator->getTotalPages());

        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForClassName(TopLevelRetainedTestModel::class, 10, 1);
        $this->assertEquals(2, $paginator->getTotalPages());
        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForModel($m, 10, 1);
        $this->assertEquals(2, $paginator->getTotalPages());

        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForClassName($m, 2, 1);
        $this->assertEquals(6, $paginator->getTotalPages());
        $paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForModel($m, 2, 1);
        $this->assertEquals(6, $paginator->getTotalPages());

        $this->assertTrue($m->delete());
        $this->assertEquals(12, MondocRetentionService::deleteMulti(['class' => TopLevelRetainedTestModel::class]));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testInsertMulti(): void
    {
        MondocRetentionService::deleteMulti([]);
        $this->assertEquals(0, MondocRetentionService::countAll());

        $m1 = new TopLevelRetainedTestModel();
        $m1->setName('foo');
        $m1->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $m2 = new TopLevelRetainedTestModel();
        $m2->setName('bar');
        $m2->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $m3 = new TopLevelRetainedTestModel();
        $m3->setName('baz');
        $m3->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertTrue(TopLevelRetainedTestService::insertMulti([$m1, $m2, $m3]));
        $this->assertEquals(3, MondocRetentionService::countAll());

        $this->assertEquals(3, TopLevelRetainedTestService::deleteMulti([]));
        $this->assertEquals(3, MondocRetentionService::deleteMulti([]));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testExpiry()
    {
        TopLevelRetainedTestService::deleteMulti([]);
        MondocRetentionService::deleteMulti([]);

        $m1 = new TopLevelRetainedTestModel();
        $m1->setName('foo');
        $m1->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $m1->setMondocRetentionExpiry(
            Date::modify(Date::nowUtc())->minus()->days(1)
        );

        $m2 = new TopLevelRetainedTestModel();
        $m2->setName('bar');
        $m2->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $m2->setMondocRetentionExpiry(
            Date::modify(Date::nowUtc())->plus()->days(1)
        );

        $m3 = new TopLevelRetainedTestModel();
        $m3->setName('baz');
        $m3->setMondocRetentionChangeMeta([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $m3->setMondocRetentionExpiry(
            Date::modify(Date::nowUtc())->plus()->days(1)
        );

        $this->assertTrue($m1->save());
        $this->assertTrue($m2->save());
        $this->assertTrue($m3->save());

        $this->assertEquals(3, MondocRetentionService::countAll());
        $this->assertEquals(1, MondocRetentionService::countRetentionModelsForModel($m1)); // it's ID based.
        $paginatorClassName = MondocRetentionService::getPaginatorForExpiredRetentionForClassName(TopLevelRetainedTestModel::class, 1, 10);
        $paginatorModel = MondocRetentionService::getPaginatorForExpiredRetentionForObject($m1, 1, 10);

        $this->assertEquals(1, $paginatorClassName->getTotalPages());
        $this->assertEquals(1, $paginatorModel->getTotalPages());

        $this->assertEquals(1, $paginatorClassName->getTotalResults());
        $this->assertEquals(1, $paginatorModel->getTotalResults());

        $pageOfResultsClassName = MondocRetentionService::getRetentionPage($paginatorClassName);
        $pageOfResultsModel = MondocRetentionService::getRetentionPage($paginatorModel);

        $this->assertCount(1, $pageOfResultsClassName);
        $this->assertCount(1, $pageOfResultsModel);

        $this->assertEquals($m1->getObjectIdString(), $pageOfResultsClassName[0]->getSourceObjectIdString());
        $this->assertEquals($m1->getObjectIdString(), $pageOfResultsModel[0]->getSourceObjectIdString());

        $paginatorClassNameWithObjectInstead = MondocRetentionService::getPaginatorForExpiredRetentionForClassName($m1, 1, 10);
        $this->assertEquals(1, $paginatorClassNameWithObjectInstead->getTotalPages());
        $this->assertEquals(1, $paginatorClassNameWithObjectInstead->getTotalResults());
        $pageOfResultsClassNameWithObjectInstead = MondocRetentionService::getRetentionPage($paginatorClassNameWithObjectInstead);
        $this->assertCount(1, $pageOfResultsClassNameWithObjectInstead);
        $this->assertEquals($m1->getObjectIdString(), $pageOfResultsClassNameWithObjectInstead[0]->getSourceObjectIdString());

        $pageFromModel = $m1->getRetentionPageByPaginator($paginatorClassNameWithObjectInstead);
        $this->assertCount(1, $pageFromModel);
        $this->assertEquals($m1->getObjectIdString(), $pageFromModel[0]->getSourceObjectIdString());

        $this->assertEquals(3, TopLevelRetainedTestService::deleteMulti([]));
        $this->assertEquals(3, MondocRetentionService::deleteMulti([]));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testIndexes()
    {
        MondocRetentionService::getCollection()->drop();
        MondocRetentionService::getCollection();
        $this->assertFalse(MondocRetentionService::hasIndexes());
        MondocRetentionService::addIndexes();
        $this->assertTrue(MondocRetentionService::hasIndexes());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MondocRetentionService::deleteMulti([]);
        TopLevelRetainedTestService::deleteMulti([]);
        SubLevelRetainedTestService::deleteMulti([]);
        parent::tearDown();
    }
}
