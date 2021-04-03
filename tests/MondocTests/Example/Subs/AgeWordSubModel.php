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
 * Class AgeWordSubModel.
 *
 * @package District5Tests\MondocTests\Example\Subs
 */
class AgeWordSubModel extends MondocAbstractSubModel
{
    /**
     * @var string
     */
    protected $word;

    /**
     * @param string $word
     *
     * @return $this
     */
    public function setWord(string $word)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }
}
