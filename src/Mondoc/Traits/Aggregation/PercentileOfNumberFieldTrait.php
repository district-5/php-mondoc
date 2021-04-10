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

use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

/**
 * Trait PercentileOfNumberFieldTrait.
 *
 * @package District5\Mondoc\Traits\Aggregation
 */
trait PercentileOfNumberFieldTrait
{
    /**
     * Get the value of the X percentile of a $fieldName by a given filter. By default the ordering is ascending (1),
     * but you can provide -1 to sort descending.
     *
     * @param string $fieldName
     * @param float  $percentile
     * @param int    $sortDirection (1 or -1)
     * @param array  $filter
     *
     * @return null|float|int
     * @noinspection PhpUnused
     */
    public function getPercentile(string $fieldName, float $percentile, int $sortDirection = 1, array $filter = [])
    {
        $collection = $this->service::getCollection($this->service);
        /* @var $collection Collection */
        $query = [
            [
                '$sort' => [
                    $fieldName => $sortDirection
                ]
            ],
            [
                '$group' => [
                    '_id' => null,
                    'doc' => [
                        '$first' => '$$ROOT'
                    ],
                    'values' => [
                        '$push' => '$'.$fieldName
                    ]
                ]
            ],
            [
                '$project' => [
                    $fieldName.'Percentile' => [
                        '$arrayElemAt' => [
                            '$values',
                            [
                                '$floor' => [
                                    '$multiply' => [
                                        $percentile,
                                        [
                                            '$size' => '$values'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if (!empty($filter)) {
            array_unshift(
                $query,
                [
                    '$match' => $filter
                ]
            );
        }
        $cursor = $collection->aggregate(
            array_values($query)
        );
        /* @var $cursor Cursor */
        $records = $cursor->toArray();
        /* @var $records BSONDocument[] */
        if (1 === count($records)) {
            $array = $records[0]->getArrayCopy();
            if (array_key_exists($fieldName.'Percentile', $array)) {
                return $array[$fieldName.'Percentile'];
            }
        }

        return null;
    }
}
