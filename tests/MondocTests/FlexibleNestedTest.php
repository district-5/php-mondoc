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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\FlexibleControlTestConfigSingleton;
use District5Tests\MondocTests\TestObjects\Model\FlexibleNestedTestModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\AgeSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\AgeWordSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodAttributesSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodSubModel;
use District5Tests\MondocTests\TestObjects\Service\FlexibleNestedTestService;

/**
 * Class FlexibleNestedTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class FlexibleNestedTest extends MondocBaseTest
{
    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testBasic()
    {
        $this->assertEquals(0, FlexibleNestedTestService::countAll());
        $mFood = new FlexibleNestedTestModel();
        $mFood->setName('test1');
        $person1 = $mFood->getPerson();
        $person1->setName('John');
        FlexibleControlTestConfigSingleton::getInstance()->setClassName(FoodSubModel::class); // only exists for testing purposes
        $food = new FoodSubModel();
        $attribute1 = new FoodAttributesSubModel();
        $attribute1->setColour('red');
        $attribute1->setSmell('good');
        $attribute2 = new FoodAttributesSubModel();
        $attribute2->setColour('green');
        $attribute2->setSmell('bad');
        $food->setAttributes([$attribute1, $attribute2]);
        $food->setFood('apple');
        $mFood->setNested($food);
        $this->assertTrue($mFood->save());

        FlexibleControlTestConfigSingleton::getInstance()->setClassName(AgeSubModel::class); // only exists for testing purposes

        $mAge = new FlexibleNestedTestModel();
        $mAge->setName('test2');
        $person2 = $mAge->getPerson();
        $person2->setName('Jane');
        $age = new AgeSubModel();
        $age->setAge(10);
        $ageWord = new AgeWordSubModel();
        $ageWord->setWord('ten');
        $age->setAgeWordModel($ageWord);
        $mAge->setNested($age);
        $this->assertTrue($mAge->save());

        FlexibleControlTestConfigSingleton::getInstance()->setClassName(FoodSubModel::class); // only exists for testing purposes
        $retrievedWithFood = FlexibleNestedTestService::getById($mFood->getObjectId());
        /** @var FlexibleNestedTestModel $retrievedWithFood */
        $this->assertInstanceOf(FoodSubModel::class, $retrievedWithFood->getNested());
        $this->assertEquals('John', $retrievedWithFood->getPerson()->getName());

        FlexibleControlTestConfigSingleton::getInstance()->setClassName(AgeSubModel::class); // only exists for testing purposes
        $retrievedWithAge = FlexibleNestedTestService::getById($mAge->getObjectId());
        /** @var FlexibleNestedTestModel $retrievedWithAge */
        $this->assertInstanceOf(AgeSubModel::class, $retrievedWithAge->getNested());
        $this->assertEquals('Jane', $retrievedWithAge->getPerson()->getName());

        $this->assertEquals(2, FlexibleNestedTestService::deleteByIds([$mFood->getObjectId(), $mAge->getObjectId()]));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        FlexibleNestedTestService::deleteMulti([]);
    }
}
