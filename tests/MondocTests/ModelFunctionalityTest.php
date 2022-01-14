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
use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\Example\DateModel;
use District5Tests\MondocTests\Example\DateService;
use District5Tests\MondocTests\Example\MyModel;
use District5Tests\MondocTests\Example\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class ModelFunctionalityTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class ModelFunctionalityTest extends MondocBaseTest
{
    public function testInflationDeflation()
    {
        $data = [
            'name' => 'Foo',
            'age' => 2
        ];
        $inflated = MyModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals(2, $inflated->getAge());

        $anId = new ObjectId();
        $data = [
            'name' => 'Foo',
            'age' => 2,
            '_id' => $anId
        ];
        $inflated = MyModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals(2, $inflated->getAge());
        $this->assertEquals($anId->__toString(), $inflated->getMongoIdString());
    }

    public function testDateMethods()
    {
        $this->initMongo();
        $this->mondoc->getCollection('date_model')->drop();

        $nowDate = new DateTime();
        $m = new DateModel();
        $m->setDate($nowDate);
        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(false)->format('Y-m-d H:i:s'));
        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(true)->toDateTime()->format('Y-m-d H:i:s'));
        $this->assertFalse($m->hasMongoId());
        $m->save();
        $this->assertTrue($m->hasMongoId());
        $found = DateService::getById($m->getMongoId());
        /* @var $found DateModel */
        $this->assertEquals($m->getMongoIdString(), $found->getMongoIdString());
        $this->assertEquals($m->getDate(false)->format('Y-m-d H:i:s'), $found->getDate(false)->format('Y-m-d H:i:s'));
        $this->assertTrue($found->delete());
    }

    public function testBasicModelMethods()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');
        $this->assertEquals(2, $m->getAge());
        $this->assertEquals('Joe', $m->getName());

        $array = $m->asArray();
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('name', $array);

        $dirty = $m->getDirty();
        $this->assertTrue(in_array('age', $dirty));
        $this->assertTrue(in_array('name', $dirty));
        $this->assertEquals(2, $array['age']);
        $this->assertEquals('Joe', $array['name']);
    }

    public function testInsertMulti()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());

        $mT = new MyModel();
        $mT->setAge(2);
        $mT->setName(uniqid());

        $this->assertFalse($m->hasMongoId());
        $this->assertFalse($mT->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());
        $this->assertFalse($mT->hasPresetMongoId());

        $this->assertTrue(MyService::insertMulti([$m, $mT]));

        $this->assertTrue($m->hasMongoId());
        $this->assertTrue($mT->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());
        $this->assertFalse($mT->hasPresetMongoId());

        $this->assertTrue($m->delete());
        $this->assertTrue($mT->delete());
    }

    public function testInsertMultiWithOneModel()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());

        $this->assertFalse($m->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());

        $this->assertTrue(MyService::insertMulti([$m]));

        $this->assertTrue($m->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());

        $this->assertTrue($m->delete());
    }

    public function testInsertMultiWithPresetIds()
    {
        $this->initMongo();

        $idOne = new ObjectId();
        $idTwo = new ObjectId();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());
        $m->setPresetMongoId($idOne);

        $mT = new MyModel();
        $mT->setAge(2);
        $mT->setName(uniqid());
        $mT->setPresetMongoId($idTwo);

        $this->assertFalse($m->hasMongoId());
        $this->assertFalse($mT->hasMongoId());
        $this->assertTrue($m->hasPresetMongoId());
        $this->assertTrue($mT->hasPresetMongoId());

        $this->assertTrue(MyService::insertMulti([$m, $mT]));

        $this->assertEquals($idOne->__toString(), $m->getMongoIdString());
        $this->assertEquals($idTwo->__toString(), $mT->getMongoIdString());

        $this->assertTrue($m->hasMongoId());
        $this->assertTrue($mT->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());
        $this->assertFalse($mT->hasPresetMongoId());

        $this->assertTrue($m->delete());
        $this->assertTrue($mT->delete());
    }

    public function testInsertMultiWithOneModelWithPresetIds()
    {
        $this->initMongo();

        $idOne = new ObjectId();

        $m = new MyModel();
        $m->setAge(1);
        $m->setName(uniqid());
        $m->setPresetMongoId($idOne);

        $this->assertFalse($m->hasMongoId());
        $this->assertTrue($m->hasPresetMongoId());

        $this->assertTrue(MyService::insertMulti([$m]));

        $this->assertEquals($idOne->__toString(), $m->getMongoIdString());

        $this->assertTrue($m->hasMongoId());
        $this->assertFalse($m->hasPresetMongoId());

        $this->assertTrue($m->delete());
    }

    public function testModelHasAndDoesNotHaveIds()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());

        // not been saved yet
        $this->assertFalse($m->hasMongoId());

        // save the model
        $this->assertTrue($m->save());

        // ensure it has a mongo id now
        $this->assertTrue($m->hasMongoId());

        // delete the model
        $this->assertTrue($m->delete());
    }

    public function testGetWhereEqualOrNotEqual()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());
        $m->save();
        $this->assertCount(1, MyService::getMultiWhereKeyEqualsValue('age', 2));
        $this->assertCount(0, MyService::getMultiWhereKeyDoesNotEqualValue('age', 2));

        $this->assertEquals($m->getMongoIdString(), MyService::getOneWhereKeyEqualsValue('age', 2)->getMongoIdString());
        $this->assertNull(MyService::getOneWhereKeyDoesNotEqualValue('age', 2));

        // delete the model
        $this->assertTrue($m->delete());
    }

    public function testModelExistence()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(12345);
        $m->setName('existence');
        $this->assertTrue($m->save());

        $this->assertTrue(MyService::exists(['age' => 12345]));
        $this->assertTrue(MyService::exists(['name' => 'existence']));

        $this->assertFalse(MyService::exists(['age' => 9876]));
        $this->assertFalse(MyService::exists(['name' => 'nope']));

        // delete the model
        $this->assertTrue($m->delete());
    }

    public function testIncrementDecrementModelShortcuts()
    {
        $this->initMongo();

        $m = new MyModel();
        $m->setAge(90);
        $m->setName($this->getUniqueKey());
        $m->save();

        $this->assertEquals(90, MyService::getById($m->getMongoId())->getAge());

        $this->assertTrue($m->incrementAge());
        $this->assertEquals(91, $m->getAge());
        $this->assertEquals(91, MyService::getById($m->getMongoId())->getAge());

        $this->assertTrue($m->decrementAge());
        $this->assertTrue($m->decrementAge());
        $this->assertEquals(89, $m->getAge());
        $this->assertEquals(89, MyService::getById($m->getMongoId())->getAge());

        // delete the model
        $this->assertTrue($m->delete());
    }

    public function testPersistAndQuery()
    {
        $this->initMongo();
        // drop the collection
        $this->mondoc->getCollection('test_model')->drop();

        $this->assertEquals(0, MyService::countAll([]));

        $name = 'Joe '.$this->getUniqueKey();
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($name);

        // save the model
        $this->assertTrue($m->save());

        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::inc($m->getMongoId(), 'age', 2));
        $this->assertEquals(4, MyService::getById($m->getMongoId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateOne(['_id' => $m->getMongoId()], ['$set' => ['age' => 2]]));
        $this->assertEquals(2, MyService::getById($m->getMongoId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateOne(['_id' => $m->getMongoId()], ['$set' => ['age' => 4]]));
        $this->assertEquals(4, MyService::getById($m->getMongoId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::dec($m->getMongoId(), 'age', 2));
        $this->assertEquals(2, MyService::getById($m->getMongoId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getAverage('age', ['name' => $m->getName()]));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        // Get the ID
        $theId = $m->getMongoIdString();

        unset($m);

        $this->assertEquals(1, MyService::countAll(['name' => $name]));
        $builder = MyService::getQueryBuilder();
        $equal = ValueEqualTo::get();
        $equal->string('name', $name);
        $builder->addQueryPart($equal);
        $this->assertEquals(1, MyService::countAllByQueryBuilder($builder));

        $find = MyService::getOneByCriteria(['name' => $name]);
        /* @var $find MyModel */
        $this->assertNotNull($find);
        $this->assertInstanceOf(MyModel::class, $find);

        $this->assertEquals($name, $find->getName());

        // ensure it has a mongo id now
        $this->assertTrue($find->hasMongoId());
        $this->assertEquals($theId, $find->getMongoIdString());

        unset($find);

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);
        $this->assertNotNull($multi[0]);
        $this->assertInstanceOf(MyModel::class, $multi[0]);

        $this->assertEquals($name, $multi[0]->getName());

        $multiByIds = MyService::getByIds([$theId]);
        $this->assertCount(1, $multiByIds);
        $this->assertNotNull($multiByIds[0]);
        $this->assertInstanceOf(MyModel::class, $multiByIds[0]);

        // ensure it has a mongo id now
        $this->assertTrue($multi[0]->hasMongoId());
        $this->assertEquals($theId, $multi[0]->getMongoIdString());

        unset($multi);

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);
        $time = microtime(false);
        $multi[0]->setName('joe-bloggs '.$time);
        $this->assertTrue($multi[0]->save());
        unset($multi);

        $multi = MyService::getMultiByCriteria(['name' => 'joe-bloggs '.$time]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);

        // delete the model
        $this->assertTrue($multi[0]->delete());

        unset($multi);
    }

    public function testAverageSumCeilWorkCorrectly()
    {
        $this->initMongo();
        // drop the collection
        $this->mondoc->getCollection('test_model')->drop();

        $this->assertEquals(0, MyService::countAll([]));

        $ages = [2 => 'Joe', 4 => 'Joe', 6 => 'Jane'];
        foreach ($ages as $age => $name) {
            $m = new MyModel();
            $m->setAge($age);
            $m->setName($name);
            $this->assertTrue($m->save());
        }
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(12, MyService::aggregate()->getSum('age'));
        $this->assertEquals(3, MyService::aggregate()->getAverage('age', ['name' => 'Joe']));
        $this->assertEquals(3, MyService::aggregate()->getAverage('age', ['name' => ['$eq' => 'Joe']]));
        $this->assertEquals(6, MyService::aggregate()->getSum('age', ['name' => 'Joe']));
        $this->assertEquals(6, MyService::aggregate()->getSum('age', ['name' => ['$eq' => 'Joe']]));

        $tmpPaginator = MyService::getPaginationQueryHelper(1, 10, []);
        $this->assertEquals(1, $tmpPaginator->getTotalPages());
        $this->assertEquals(1, $tmpPaginator->getCurrentPage());
        $this->assertEquals(10, $tmpPaginator->getLimit());
        $this->assertEquals(0, $tmpPaginator->getSkip());
    }

    public function testPagination()
    {
        $this->initMongo();
        // drop the collection
        $this->mondoc->getCollection('test_model')->drop();

        $builder = new QueryBuilder();

        $this->assertEquals(0, MyService::countAll([]));
        $this->assertEquals(0, MyService::countAllByQueryBuilder($builder));

        $ages = [2 => 'Joe', 4 => 'Joe', 6 => 'Jane'];
        foreach ($ages as $age => $name) {
            $m = new MyModel();
            $m->setAge($age);
            $m->setName($name);
            $this->assertTrue($m->save());
        }

        $paginator = MyService::getPaginationQueryHelper(1, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());

        $ids = [];
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getMongoIdString(), $ids));
        $ids[] = $results[0]->getMongoIdString();

        $paginator = MyService::getPaginationQueryHelper(2, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getMongoIdString(), $ids));
        $ids[] = $results[0]->getMongoIdString();

        $paginator = MyService::getPaginationQueryHelper(3, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getMongoIdString(), $ids));

        $paginator = MyService::getPaginationQueryHelper(1, 1, ['name' => 'Joe']);
        $this->assertEquals(2, $paginator->getTotalPages());

        $ids = [];
        $results = MyService::getPage($paginator, ['name' => 'Joe'], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getMongoIdString(), $ids));
        $ids[] = $results[0]->getMongoIdString();

        $paginator = MyService::getPaginationQueryHelper(2, 1, ['name' => 'Joe']);
        $this->assertEquals(2, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, ['name' => 'Joe'], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getMongoIdString(), $ids));
    }

    public function testPercentile()
    {
        $this->initMongo();
        // drop the collection
        $this->mondoc->getCollection('test_model')->drop();

        $i = 0;
        $models = [];
        while ($i < 10) {
            $i++;
            $m = new MyModel();
            $m->setName(uniqid());
            $m->setAge($i);
            $models[] = $m;
        }
        MyService::insertMulti($models);
        foreach ($models as $model) {
            $this->assertTrue($model->hasMongoId());
        }

        $this->assertEquals(1, MyService::aggregate()->getPercentile('age', .0));
        $this->assertEquals(2, MyService::aggregate()->getPercentile('age', .1));
        $this->assertEquals(3, MyService::aggregate()->getPercentile('age', .2));
        $this->assertEquals(4, MyService::aggregate()->getPercentile('age', .3));
        $this->assertEquals(5, MyService::aggregate()->getPercentile('age', .4));
        $this->assertEquals(6, MyService::aggregate()->getPercentile('age', .5));
        $this->assertEquals(7, MyService::aggregate()->getPercentile('age', .6));
        $this->assertEquals(8, MyService::aggregate()->getPercentile('age', .7));
        $this->assertEquals(9, MyService::aggregate()->getPercentile('age', .8));
        $this->assertEquals(10, MyService::aggregate()->getPercentile('age', .9));
        $this->assertNull(MyService::aggregate()->getPercentile('age', 1));

        $this->assertEquals(10, MyService::aggregate()->getPercentile('age', .0, -1));
        $this->assertEquals(9, MyService::aggregate()->getPercentile('age', .1, -1));
        $this->assertEquals(8, MyService::aggregate()->getPercentile('age', .2, -1));
        $this->assertEquals(7, MyService::aggregate()->getPercentile('age', .3, -1));
        $this->assertEquals(6, MyService::aggregate()->getPercentile('age', .4, -1));
        $this->assertEquals(5, MyService::aggregate()->getPercentile('age', .5, -1));
        $this->assertEquals(4, MyService::aggregate()->getPercentile('age', .6, -1));
        $this->assertEquals(3, MyService::aggregate()->getPercentile('age', .7, -1));
        $this->assertEquals(2, MyService::aggregate()->getPercentile('age', .8, -1));
        $this->assertEquals(1, MyService::aggregate()->getPercentile('age', .9, -1));
    }
}
