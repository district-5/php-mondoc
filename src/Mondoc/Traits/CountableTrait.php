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

use MongoDB\Collection;

/**
 * Trait CountableTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait CountableTrait
{
    /**
     * Count all matching documents in a collection using a given criteria, using given options.
     *
     * @param array $query   (optional)
     * @param array $options (optional)
     *
     * @return int
     *
     * @see CountableTrait::countAll()
     */
    public static function countInCollection($query = [], $options = []): int
    {
        return self::countAll($query, $options);
    }

    /**
     * Count all documents in the collection where a $key = $value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool|int|mixed
     * @noinspection PhpUnused
     */
    public static function countWhereKeyEqualsValue(string $key, $value)
    {
        return self::countInCollection([
            $key => $value
        ]);
    }

    /**
     * Count all matching documents in a collection using a given criteria, using given options.
     *
     * @param array $query   (optional)
     * @param array $options (optional)
     *
     * @return int
     * @noinspection PhpUnused
     */
    public static function countAll($query = [], $options = []): int
    {
        $collection = self::getCollection(
            get_called_class()
        );
        // @var $collection Collection
        return $collection->countDocuments(
            $query,
            $options
        );
    }
}
