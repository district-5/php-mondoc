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

namespace District5Tests\MondocTests\Example;

use DateTime;
use District5\Mondoc\Model\MondocAbstractModel;
use District5Tests\MondocTests\Example\Subs\FoodSubModel;
use MongoDB\BSON\UTCDateTime;

/**
 * Class DateModel.
 *
 * @package MyNs\Model
 */
class SingleAndMultiNestedModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ?FoodSubModel
     */
    protected $food = null;

    /**
     * @var FoodSubModel[]
     */
    protected $foods = [];

    /**
     * @var array
     */
    protected $friends = [];

    /**
     * @var array|string[]
     */
    protected array $mondocNested = [
        'food' => FoodSubModel::class,
        'foods' => FoodSubModel::class . '[]'
    ];

    /**
     * @param array $friends
     */
    public function setFriends(array $friends): void
    {
        $this->friends = $friends;
        $this->addDirty('friends');
    }

    /**
     * @return array
     */
    public function getFriends(): array
    {
        return $this->friends;
    }

    /**
     * @param FoodSubModel|null $food
     */
    public function setFood(?FoodSubModel $food): void
    {
        $this->addDirty('food');
        $this->food = $food;
    }

    /**
     * @param FoodSubModel[] $foods
     */
    public function setFoods(array $foods): void
    {
        $this->addDirty('foods');
        $this->foods = $foods;
    }

    /**
     * @return FoodSubModel|null
     */
    public function getFood(): ?FoodSubModel
    {
        return $this->food;
    }

    /**
     * @return FoodSubModel[]
     */
    public function getFoods(): array
    {
        return $this->foods;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->addDirty('name');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars()
    {
        // TODO: Implement
    }
}