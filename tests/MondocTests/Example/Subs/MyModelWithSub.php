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

namespace District5Tests\MondocTests\Example\Subs;

use District5\Mondoc\Model\MondocAbstractModel;
use District5Tests\MondocTests\Example\MySubService;

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
    protected $name;

    /**
     * @var AgeSubModel
     */
    protected $age;

    /**
     * @var FoodSubModel[]
     */
    protected $foods = [];

    /**
     * @var string[]
     */
    protected $keyToClassMap = [
        'age' => AgeSubModel::class,
        'foods' => FoodSubModel::class.'[]'
    ];

    /**
     * @param FoodSubModel $food
     *
     * @return $this
     */
    public function addFood(FoodSubModel $food)
    {
        $this->foods[] = $food;

        return $this;
    }

    /**
     * @param AgeSubModel $age
     *
     * @return $this
     */
    public function setAge(AgeSubModel $age)
    {
        $this->age = $age;
        $this->addDirty('age', $this->age);

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
     * @return string
     */
    public function getName()
    {
        return trim($this->name);
    }

    /**
     * @return FoodSubModel[]
     */
    public function getFoods()
    {
        return $this->foods;
    }

    /**
     * @param string $val
     *
     * @return $this
     */
    public function setName(string $val)
    {
        $this->name = trim($val);
        $this->addDirty('name', trim($val));

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return MySubService::saveModel($this);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return MySubService::deleteModel($this);
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
