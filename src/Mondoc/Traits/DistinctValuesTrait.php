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
 * Trait DistinctValuesTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait DistinctValuesTrait
{
    /**
     * Get an array of all distinct values for a given key in a collection, optionally providing a filter and options.
     *
     * @param string $key
     * @param array  $filter  (optional)
     * @param array  $options (optional)
     *
     * @return array
     */
    public static function getDistinctValuesForKey(string $key, array $filter = [], array $options = []): array
    {
        $collection = self::getCollection(
            get_called_class()
        );
        // @var $collection Collection
        return $collection->distinct($key, $filter, $options);
    }
}
