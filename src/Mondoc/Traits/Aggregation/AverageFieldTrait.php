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

namespace District5\Mondoc\Traits\Aggregation;

/**
 * Trait AverageFieldTrait.
 *
 * @package District5\Mondoc\Traits\Aggregation
 */
trait AverageFieldTrait
{
    /**
     * Get the average of a $fieldName by a given criteria.
     *
     * @param string $fieldName
     * @param array  $criteria
     *
     * @return float|int
     * @noinspection PhpUnused
     */
    public static function getAverage(string $fieldName, array $criteria = [])
    {
        $collection = self::getCollection(get_called_class());
        // @var $collection Collection
        $query = [
            [
                '$group' => [
                    '_id' => null,
                    $fieldName => ['$avg' => sprintf('$%s', $fieldName)]
                ]
            ]
        ];
        if (!empty($criteria)) {
            $query[1] = array_merge([], $query[0]);
            $query[0] = [
                '$match' => $criteria
            ];
        }
        $cursor = $collection->aggregate(
            array_values($query)
        );
        // @var $cursor Cursor
        $records = $cursor->toArray();
        // @var $records BSONDocument[]
        if (1 === count($records)) {
            $array = $records[0]->getArrayCopy();
            if (array_key_exists($fieldName, $array)) {
                return $array[$fieldName];
            }
        }

        return 0;
    }
}
