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

namespace District5\Mondoc\Traits\Operators;

use MongoDB\BSON\ObjectId;

/**
 * Trait PullTrait.
 *
 * @package District5\Mondoc\Traits\Operators
 */
trait PullTrait
{
    /**
     * Remove a normalised (not object or array) value from an array on via an atomic operation.
     *
     * @param array $filter
     * @param string $field
     * @param string|int|bool $value
     * @return bool
     * @noinspection PhpUnused
     */
    protected static function pullFromArrayWithFilter(array $filter, string $field, $value): bool
    {
        return self::updateOne(
            $filter,
            [
                '$pull' => [$field => $value]
            ]
        );
    }

    /**
     * Remove a single normalised (not object or array) value from an array on a single document via an atomic
     * operation.
     *
     * @param ObjectId $id
     * @param string $field
     * @param string|int|bool $value
     * @return bool
     * @noinspection PhpUnused
     */
    protected static function pullFromArrayById(ObjectId $id, string $field, $value): bool
    {
        $query = [
            '_id' => $id
        ];
        return self::updateOne(
            $query,
            [
                '$pull' => [$field => $value]
            ]
        );
    }
}
