<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

/**
 * District5 - Mondoc
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 */

namespace District5Tests\MondocTests;

use District5Tests\MondocTests\Example\MyModel;
use District5Tests\MondocTests\Example\MyService;

/**
 * Class ModelFunctionalityTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class ModelFunctionalityTest extends MondocBaseTest
{
//    public function testInflationDeflation()
//    {
//        $data = [
//            'name' => 'Foo',
//            'age' => 2
//        ];
//        $inflated = MyModel::inflateSingleArray($data);
//        $this->assertEquals('Foo', $inflated->getName());
//        $this->assertEquals(2, $inflated->getAge());
//
//        $anId = new ObjectId();
//        $data = [
//            'name' => 'Foo',
//            'age' => 2,
//            '_id' => $anId
//        ];
//        $inflated = MyModel::inflateSingleArray($data);
//        $this->assertEquals('Foo', $inflated->getName());
//        $this->assertEquals(2, $inflated->getAge());
//        $this->assertEquals($anId->__toString(), $inflated->getMongoIdString());
//    }
//
//    public function testDateMethods()
//    {
//        $nowDate = new DateTime();
//        $m = new DateModel();
//        $m->setDate($nowDate);
//        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(false)->format('Y-m-d H:i:s'));
//        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(true)->toDateTime()->format('Y-m-d H:i:s'));
//    }
//
//    public function testBasicModelMethods()
//    {
//        $m = new MyModel();
//        $m->setAge(2);
//        $m->setName('Joe');
//        $this->assertEquals(2, $m->getAge());
//        $this->assertEquals('Joe', $m->getName());
//
//        $array = $m->asArray();
//        $this->assertArrayHasKey('age', $array);
//        $this->assertArrayHasKey('name', $array);
//
//        $dirty = $m->getDirty();
//        $this->assertTrue(in_array('age', $dirty));
//        $this->assertTrue(in_array('name', $dirty));
//        $this->assertEquals(2, $array['age']);
//        $this->assertEquals('Joe', $array['name']);
//    }
//
//    public function testInsertMulti()
//    {
//        $this->initMongo();
//
//        $m = new MyModel();
//        $m->setAge(1);
//        $m->setName(uniqid());
//
//        $mT = new MyModel();
//        $mT->setAge(2);
//        $mT->setName(uniqid());
//
//        $this->assertFalse($m->hasMongoId());
//        $this->assertFalse($mT->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//        $this->assertFalse($mT->hasPresetMongoId());
//
//        $this->assertTrue(MyService::insertMulti([$m, $mT]));
//
//        $this->assertTrue($m->hasMongoId());
//        $this->assertTrue($mT->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//        $this->assertFalse($mT->hasPresetMongoId());
//
//        $this->assertTrue($m->delete());
//        $this->assertTrue($mT->delete());
//    }
//
//    public function testInsertMultiWithOneModel()
//    {
//        $this->initMongo();
//
//        $m = new MyModel();
//        $m->setAge(1);
//        $m->setName(uniqid());
//
//        $this->assertFalse($m->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//
//        $this->assertTrue(MyService::insertMulti([$m]));
//
//        $this->assertTrue($m->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//
//        $this->assertTrue($m->delete());
//    }
//
//    public function testInsertMultiWithPresetIds()
//    {
//        $this->initMongo();
//
//        $idOne = new ObjectId();
//        $idTwo = new ObjectId();
//
//        $m = new MyModel();
//        $m->setAge(1);
//        $m->setName(uniqid());
//        $m->setPresetMongoId($idOne);
//
//        $mT = new MyModel();
//        $mT->setAge(2);
//        $mT->setName(uniqid());
//        $mT->setPresetMongoId($idTwo);
//
//        $this->assertFalse($m->hasMongoId());
//        $this->assertFalse($mT->hasMongoId());
//        $this->assertTrue($m->hasPresetMongoId());
//        $this->assertTrue($mT->hasPresetMongoId());
//
//        $this->assertTrue(MyService::insertMulti([$m, $mT]));
//
//        $this->assertEquals($idOne->__toString(), $m->getMongoIdString());
//        $this->assertEquals($idTwo->__toString(), $mT->getMongoIdString());
//
//        $this->assertTrue($m->hasMongoId());
//        $this->assertTrue($mT->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//        $this->assertFalse($mT->hasPresetMongoId());
//
//        $this->assertTrue($m->delete());
//        $this->assertTrue($mT->delete());
//    }
//
//    public function testInsertMultiWithOneModelWithPresetIds()
//    {
//        $this->initMongo();
//
//        $idOne = new ObjectId();
//
//        $m = new MyModel();
//        $m->setAge(1);
//        $m->setName(uniqid());
//        $m->setPresetMongoId($idOne);
//
//        $this->assertFalse($m->hasMongoId());
//        $this->assertTrue($m->hasPresetMongoId());
//
//        $this->assertTrue(MyService::insertMulti([$m]));
//
//        $this->assertEquals($idOne->__toString(), $m->getMongoIdString());
//
//        $this->assertTrue($m->hasMongoId());
//        $this->assertFalse($m->hasPresetMongoId());
//
//        $this->assertTrue($m->delete());
//    }
//
//    public function testModelHasAndDoesNotHaveIds()
//    {
//        $this->initMongo();
//
//        $m = new MyModel();
//        $m->setAge(2);
//        $m->setName($this->getUniqueKey());
//
//        // not been saved yet
//        $this->assertFalse($m->hasMongoId());
//
//        // save the model
//        $this->assertTrue($m->save());
//
//        // ensure it has a mongo id now
//        $this->assertTrue($m->hasMongoId());
//
//        // delete the model
//        $this->assertTrue($m->delete());
//    }

    public function testPersistAndQuery()
    {
        $this->initMongo();
        // drop the collection
        $this->mondoc->getCollection('test_model')->drop();

        $this->assertEquals(0, MyService::countAll([]));
        $this->assertEquals(0, MyService::countInCollection([]));

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

        $this->assertTrue(MyService::dec($m->getMongoId(), 'age', 2));
        $this->assertEquals(2, MyService::getById($m->getMongoId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getAverage('age', ['name' => $m->getName()]));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        // Get the ID
        $theId = $m->getMongoIdString();

        unset($m);

        $this->assertEquals(1, MyService::countAll(['name' => $name]));

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
