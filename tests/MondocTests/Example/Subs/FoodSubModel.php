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
 * Class FoodSubModel.
 *
 * @package District5Tests\MondocTests\Example\Subs
 */
class FoodSubModel extends MondocAbstractSubModel
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setFood(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getFood(): string
    {
        return $this->type;
    }
}
