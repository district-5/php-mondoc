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

use District5\Mondoc\Model\MondocAbstractSubModel;

/**
 * Class AgeSubModel.
 *
 * @package District5Tests\MondocTests\Example\Subs
 */
class AgeSubModel extends MondocAbstractSubModel
{
    /**
     * @var int
     */
    protected $age = 0;

    /**
     * @var AgeWordSubModel
     */
    protected $wordModel;

    /**
     * @var string[]
     */
    protected $keyToClassMap = [
        'wordModel' => AgeWordSubModel::class
    ];

    /**
     * @return AgeWordSubModel
     */
    public function getAgeWordModel(): AgeWordSubModel
    {
        if (null === $this->wordModel) {
            $this->wordModel = new AgeWordSubModel();
        }

        return $this->wordModel;
    }

    /**
     * @param AgeWordSubModel $model
     *
     * @return $this
     */
    public function setAgeWordModel(AgeWordSubModel $model): AgeSubModel
    {
        $this->wordModel = $model;

        return $this;
    }

    /**
     * @param int $age
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setAge(int $age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }
}
