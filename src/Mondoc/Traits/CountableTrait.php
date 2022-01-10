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

use District5\MondocBuilder\QueryBuilder;

/**
 * Trait CountableTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait CountableTrait
{
    /**
     * @deprecated
     * @see CountableTrait::countAll()
     */
    public static function countInCollection(array $query = [], array $options = []): int
    {
        return self::countAll($query, $options);
    }

    /**
     * Count all matching documents in a collection using a given filter, using given options.
     *
     * @param array $query (optional)
     * @param array $options (optional)
     *
     * @return int
     * @noinspection PhpUnused
     */
    public static function countAll(array $query = [], array $options = []): int
    {
        $collection = self::getCollection(
            get_called_class()
        );
        return $collection->countDocuments(
            $query,
            $options
        );
    }

    /**
     * Count all documents in the collection where a $key = $value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int
     * @noinspection PhpUnused
     */
    public static function countWhereKeyEqualsValue(string $key, $value): int
    {
        return self::countAll([
            $key => $value
        ]);
    }

    /**
     * Count all matching documents in a collection using a QueryBuilder
     *
     * @param QueryBuilder $builder
     *
     * @return int
     * @noinspection PhpUnused
     */
    public static function countAllByQueryBuilder(QueryBuilder $builder): int
    {
        $builder = clone $builder;
        $builder->getOptions()->setSortBy([]);
        $collection = self::getCollection(
            get_called_class()
        );
        return $collection->countDocuments(
            $builder->getArrayCopy(),
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Count all matching documents in a collection using a given filter, using given options.
     *
     * @param array $options (optional)
     *
     * @return int
     * @noinspection PhpUnused
     */
    public static function estimateDocumentCount(array $options = []): int
    {
        $collection = self::getCollection(
            get_called_class()
        );
        return $collection->estimatedDocumentCount(
            $options
        );
    }
}
