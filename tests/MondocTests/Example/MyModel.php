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

namespace District5Tests\MondocTests\Example;

use District5\Mondoc\Model\MondocAbstractModel;

/**
 * Class MyModel.
 *
 * @package MyNs\Model
 */
class MyModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $age = 0;

    /**
     * @param int $age
     *
     * @return $this
     */
    public function setAge(int $age)
    {
        $this->age = $age;
        $this->addDirty('age', $this->age);

        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
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
     * This method must return the array to insert into Mongo.
     *
     * @return array
     */
    public function asArray(): array
    {
        $this->assignDefaultVars();

        return [
            'name' => $this->getName(),
            'age' => $this->getAge()
        ];
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
