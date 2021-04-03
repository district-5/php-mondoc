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

namespace District5\Mondoc\Traits\Atomic;

use MongoDB\BSON\ObjectId;

/**
 * Trait DecrementTrait.
 *
 * @package District5\Mondoc\Traits\Atomic
 */
trait DecrementTrait
{
    /**
     * Decrement a field by a given delta, using a whole number as the delta. IE passing `1` would DECREASE a
     * number by 1.
     *
     * @param ObjectId $id
     * @param string   $field
     * @param int      $delta
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public static function dec(ObjectId $id, string $field, int $delta = 1): bool
    {
        return self::atomic(
            $id,
            ['$inc' => [$field => ($delta - ($delta * 2))]]
        );
    }
}
