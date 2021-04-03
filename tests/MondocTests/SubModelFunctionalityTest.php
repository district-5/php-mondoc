<?php

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

use District5Tests\MondocTests\Example\MySubService;
use District5Tests\MondocTests\Example\Subs\AgeSubModel;
use District5Tests\MondocTests\Example\Subs\AgeWordSubModel;
use District5Tests\MondocTests\Example\Subs\FoodSubModel;
use District5Tests\MondocTests\Example\Subs\MyModelWithSub;

/**
 * Class SubModelFunctionalityTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class SubModelFunctionalityTest extends MondocBaseTest
{
    public function testBasicModelMethods()
    {
        $this->initMongo();

        $ageModel = new AgeSubModel();
        $ageModel->setAge(2);

        $wordModel = new AgeWordSubModel();
        $wordModel->setWord('two');

        $ageModel->setAgeWordModel($wordModel);

        $m = new MyModelWithSub();
        $m->setAge($ageModel);
        $m->setName('Joe');

        $foods = ['apple', 'pear', 'oranges'];
        foreach ($foods as $food) {
            $f = new FoodSubModel();
            $f->setFood($food);
            $m->addFood($f);
        }

        $data = $m->asArray();
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('foods', $data);
        $this->assertArrayHasKey('age', $data);
        $this->assertCount(2, $data['age']);
        $this->assertCount(3, $data['foods']);
        $this->assertArrayHasKey('age', $data['age']);
        $this->assertArrayHasKey('wordModel', $data['age']);

        $m->save();
        $id = $m->getMongoId();

        $newM = MySubService::getById($id);
        // @var $newM MyModelWithSub
        $this->assertEquals('Joe', $newM->getName());
        $this->assertInstanceOf(AgeSubModel::class, $newM->getAge());
        $this->assertEquals(2, $newM->getAge()->getAge());
        $this->assertCount(3, $newM->getFoods());
        $this->assertEquals('two', $newM->getAge()->getAgeWordModel()->getWord());
        $this->assertInstanceOf(FoodSubModel::class, $newM->getFoods()[0]);
        $this->assertInstanceOf(FoodSubModel::class, $newM->getFoods()[1]);
        $this->assertInstanceOf(FoodSubModel::class, $newM->getFoods()[2]);

        $this->assertEquals('apple', $newM->getFoods()[0]->getFood());
        $this->assertEquals('pear', $newM->getFoods()[1]->getFood());
        $this->assertEquals('oranges', $newM->getFoods()[2]->getFood());

        $this->assertEquals($id->__toString(), $newM->getMongoIdString());
        MySubService::getCollection(MySubService::class)->drop();
    }

    public function testMulti()
    {
        MySubService::getCollection(MySubService::class)->drop();
        $mOne = new MyModelWithSub();
        $mOne->setName('One');
        $ageOne = new AgeSubModel();
        $ageOne->setAge(1);
        $foodOne = new FoodSubModel();
        $foodOne->setFood('Carrots');
        $mOne->addFood($foodOne);
        $mOne->setAge($ageOne);

        $mTwo = new MyModelWithSub();
        $mTwo->setName('Two');
        $ageTwo = new AgeSubModel();
        $ageTwo->setAge(2);
        $foodTwo = new FoodSubModel();
        $foodTwo->setFood('Beans');
        $mTwo->addFood($foodTwo);
        $mTwo->setAge($ageTwo);

        $mOne->save();
        $mTwo->save();

        $models = MySubService::getMultiByCriteria(['_id' => ['$in' => [$mOne->getMongoId(), $mTwo->getMongoId()]]]);
        // @var $models MyModelWithSub[]

        $this->assertEquals($mOne->getMongoIdString(), $models[0]->getMongoIdString());
        $this->assertEquals($mTwo->getMongoIdString(), $models[1]->getMongoIdString());
        $this->assertEquals($mOne->getAge()->getAge(), $models[0]->getAge()->getAge());
        $this->assertEquals($mTwo->getAge()->getAge(), $models[1]->getAge()->getAge());

        $this->assertEquals($mOne->getFoods()[0]->getFood(), $models[0]->getFoods()[0]->getFood());
        $this->assertEquals($mTwo->getFoods()[0]->getFood(), $models[1]->getFoods()[0]->getFood());
        MySubService::getCollection(MySubService::class)->drop();
    }
}
