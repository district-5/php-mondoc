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

use District5Tests\MondocTests\TestObjects\Model\SingleAndMultiNestedModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodAttributesSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodSubModel;
use District5Tests\MondocTests\TestObjects\Service\SingleAndMultiNestedService;

/**
 * Class SingleAndNestedTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class SingleAndNestedTest extends MondocBaseTest
{
    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function testFull()
    {
        $this->initMongo();

        $m = new SingleAndMultiNestedModel();
        $m->setName('foo');
        $food = new FoodSubModel();
        $food->setFood('bread');

        $attributes1 = new FoodAttributesSubModel();
        $attributes1->setColour('white');
        $attributes1->setSmell('wheat');
        $this->assertEquals('white', $attributes1->getColour());
        $this->assertEquals('wheat', $attributes1->getSmell());
        $food->setAttributes([$attributes1]);

        $m->setFood($food);

        $food2 = new FoodSubModel();
        $food2->setFood('beef');

        $attributes2 = new FoodAttributesSubModel();
        $attributes2->setColour('red');
        $attributes2->setSmell('rich');
        $food2->setAttributes([$attributes2]);

        $food3 = new FoodSubModel();
        $food3->setFood('chicken');

        $attributes3 = new FoodAttributesSubModel();
        $attributes3->setColour('pink');
        $attributes3->setSmell('chicken-like');
        $food3->setAttributes([$attributes3]);

        $m->setFoods([$food2, $food3]);

        $m->setFriends(['Joe', 'Jane']);

        $this->assertTrue($m->save());

        $record = SingleAndMultiNestedService::getById($m->getObjectId());
        $this->assertEquals('foo', $record->getName());
        $this->assertEquals('bread', $record->getFood()->getFood());
        $this->assertEquals('white', $record->getFood()->getAttributes()[0]->getColour());
        $this->assertEquals('wheat', $record->getFood()->getAttributes()[0]->getSmell());
        $this->assertEquals('beef', $record->getFoods()[0]->getFood());
        $this->assertEquals('red', $record->getFoods()[0]->getAttributes()[0]->getColour());
        $this->assertEquals('rich', $record->getFoods()[0]->getAttributes()[0]->getSmell());
        $this->assertEquals('chicken', $record->getFoods()[1]->getFood());
        $this->assertEquals('pink', $record->getFoods()[1]->getAttributes()[0]->getColour());
        $this->assertEquals('chicken-like', $record->getFoods()[1]->getAttributes()[0]->getSmell());
        $this->assertEquals('Joe', $record->getFriends()[0]);
        $this->assertEquals('Jane', $record->getFriends()[1]);

        // Delete the document
        $this->assertTrue($m->delete());
    }
}
