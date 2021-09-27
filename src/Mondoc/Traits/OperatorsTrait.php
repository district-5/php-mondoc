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

namespace District5\Mondoc\Traits;

use District5\Mondoc\Traits\Operators\PullTrait;
use District5\Mondoc\Traits\Operators\PushTrait;

/**
 * Trait OperatorsTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait OperatorsTrait
{
    use PullTrait;
    use PushTrait;
}
