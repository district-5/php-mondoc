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
 * Trait IncrementTrait.
 *
 * @package District5\Mondoc\Traits\Atomic
 */
trait IncrementTrait
{
    /**
     * Increment a field by a given delta. Can also handle negative numbers to decrement.
     *
     * @param ObjectId $id
     * @param string $field
     * @param int $delta
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public static function inc(ObjectId $id, string $field, int $delta = 1): bool
    {
        return self::atomic(
            $id,
            ['$inc' => [$field => $delta]]
        );
    }
}
