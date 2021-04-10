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
 * Trait SumFieldTrait.
 *
 * @package District5\Mondoc\Traits\Aggregation
 */
trait SumFieldTrait
{
    /**
     * Get the sum of a $fieldName by a given filter.
     *
     * @param string $fieldName
     * @param array  $filter
     *
     * @return float|int
     * @noinspection PhpUnused
     * @noinspection DuplicatedCode
     */
    public function getSum(string $fieldName, array $filter = [])
    {
        $collection = $this->service::getCollection($this->service);
        /* @var $collection Collection */

        $query = [
            [
                '$group' => [
                    '_id' => null,
                    $fieldName => [
                        '$sum' => '$'.$fieldName
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
            if (array_key_exists($fieldName, $array)) {
                return $array[$fieldName];
            }
        }

        return 0;
    }
}
