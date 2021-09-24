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

use District5\Mondoc\Traits\Atomic\DecrementTrait;
use District5\Mondoc\Traits\Atomic\IncrementTrait;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\UpdateResult;

/**
 * Trait AtomicTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait AtomicTrait
{
    use IncrementTrait;
    use DecrementTrait;

    /**
     * Perform an atomic operation.
     *
     * @param ObjectId $id
     * @param array    $query
     *
     * @return bool
     */
    protected static function atomic(ObjectId $id, array $query): bool
    {
        return self::updateOne(
            ['_id' => $id],
            $query
        );
    }

    /**
     * Push a normalised (not object or array) value into an array on a single document via an atomic operation.
     * The value will only appear if it doesn't already exist. Otherwise, specify `$distinct = false`
     *
     * @param ObjectId $id
     * @param string $field
     * @param string $value
     * @param bool $distinct
     * @return bool
     */
    protected static function pushIntoArraySingleDocument(ObjectId $id, string $field, string $value, bool $distinct = true): bool
    {
        $query = [
            '_id' => $id
        ];
        if ($distinct === true) {
            $query[$field] = ['$ne' => $value];
        }
        return self::updateOne(
            $query,
            [
                '$push' => [$field => $value]
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
     */
    protected static function pullFromArraySingleDocument(ObjectId $id, string $field, $value): bool
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

    /**
     * Push a single normalised (not object or array) value into an array via an atomic operation.
     *
     * @param array $filter
     * @param string $field
     * @param string $value
     * @return bool
     */
    protected static function pushIntoArrayWithFilter(array $filter, string $field, string $value): bool
    {
        return self::updateOne(
            $filter,
            [
                '$push' => [$field => $value]
            ]
        );
    }

    /**
     * Remove a normalised (not object or array) value from an array on via an atomic operation.
     *
     * @param array $filter
     * @param string $field
     * @param string|int|bool $value
     * @return bool
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
}
