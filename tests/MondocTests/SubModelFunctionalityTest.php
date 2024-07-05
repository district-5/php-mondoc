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

use District5Tests\MondocTests\TestObjects\Model\MyModelWithSub;
use District5Tests\MondocTests\TestObjects\Model\Subs\AgeSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\AgeWordSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodSubModel;
use District5Tests\MondocTests\TestObjects\Service\MySubService;

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
        $id = $m->getObjectId();

        $newM = MySubService::getById($id);
        /* @var $newM MyModelWithSub */
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

        $this->assertEquals($id->__toString(), $newM->getObjectIdString());
    }

    public function testMulti()
    {

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

        $models = MySubService::getMultiByCriteria(['_id' => ['$in' => [$mOne->getObjectId(), $mTwo->getObjectId()]]]);
        /* @var $models MyModelWithSub[] */

        $this->assertEquals($mOne->getObjectIdString(), $models[0]->getObjectIdString());
        $this->assertEquals($mTwo->getObjectIdString(), $models[1]->getObjectIdString());
        $this->assertEquals($mOne->getAge()->getAge(), $models[0]->getAge()->getAge());
        $this->assertEquals($mTwo->getAge()->getAge(), $models[1]->getAge()->getAge());

        $this->assertEquals($mOne->getFoods()[0]->getFood(), $models[0]->getFoods()[0]->getFood());
        $this->assertEquals($mTwo->getFoods()[0]->getFood(), $models[1]->getFoods()[0]->getFood());
    }
}
