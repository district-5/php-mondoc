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

namespace District5Tests\MondocTests\Example\Subs;

use District5\Mondoc\Db\Model\MondocAbstractModel;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Class MyModelWithSub.
 *
 * @package District5Tests\MondocTests\Example\Subs
 */
class MyModelWithSub extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var AgeSubModel|BSONDocument
     */
    protected BSONDocument|AgeSubModel $age;

    /**
     * @var FoodSubModel[]
     */
    protected BSONArray|array $foods = [];

    /**
     * @var string[]
     */
    protected array $mondocNested = [
        'age' => AgeSubModel::class,
        'foods' => FoodSubModel::class . '[]'
    ];

    /**
     * @param FoodSubModel $food
     *
     * @return $this
     */
    public function addFood(FoodSubModel $food): static
    {
        $this->foods[] = $food;

        return $this;
    }

    /**
     * @return AgeSubModel
     */
    public function getAge(): AgeSubModel
    {
        return $this->age;
    }

    /**
     * @param AgeSubModel $age
     *
     * @return $this
     */
    public function setAge(AgeSubModel $age): static
    {
        $this->age = $age;
        $this->addDirty('age');

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return trim($this->name);
    }

    /**
     * @param string $val
     *
     * @return $this
     */
    public function setName(string $val): static
    {
        $this->name = trim($val);
        $this->addDirty('name');

        return $this;
    }

    /**
     * @return FoodSubModel[]
     */
    public function getFoods(): array
    {
        return $this->foods;
    }

    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars(): void
    {
        // TODO: Implement
    }
}
