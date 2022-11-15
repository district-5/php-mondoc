<?php
/**
 * District5 Mondoc Library
 *
 * @author      District5 <hello@district5.co.uk>
 * @copyright   District5 <hello@district5.co.uk>
 * @link        https://www.district5.co.uk
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace District5\Mondoc\Db\Service\Traits\Aggregation;

use District5\Mondoc\Dto\AggregateFinancialCandleDto;
use District5\Mondoc\Helper\MondocTypes;
use InvalidArgumentException;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

/**
 * Trait FinancialCandlesTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Aggregation
 */
trait FinancialCandlesTrait
{
    /**
     * Get a list of candles by a given query.
     *
     * @param array $filter
     * @param array $group
     * @param string $priceField
     * @param string $dateField
     * @param int $sortDirection
     * @param int $minuteGranularity
     * @param int|null $limitNumber
     * @return AggregateFinancialCandleDto[]
     * @noinspection PhpUnused
     * @noinspection DuplicatedCode
     *
     * @example $this->getCandles(
     *      [
     *          'pair' => 'BTC-USD',
     *          'exchange' => 'some-crypto-exchange'
     *      ],
     *      [
     *          'pair' => '$pair'
     *      ],
     *      'price',
     *      'date',
     *      5,
     *      1, // Sort direction, 1 or -1
     *      100
     * );
     */
    public function getFinancialXMinuteCandles(array $filter, array $group, string $priceField, string $dateField, int $minuteGranularity, int $sortDirection, ?int $limitNumber = null): array
    {
        if (!in_array($sortDirection, [1, -1])) {
            throw new InvalidArgumentException(
                'Sort direction must be either 1 or -1'
            );
        }
        $collection = $this->service::getCollection($this->service);
        /* @var $collection Collection */
        $tmpGroup = array_merge($group, []);
        foreach ($group as $k => $v) {
            $tmpGroup[$k] = $v;
        }
        $tmpGroup[$dateField] = [
            '$dateTrunc' => [
                'date' => '$' . $dateField,
                'unit' => 'minute',
                'binSize' => $minuteGranularity
            ]
        ];

        $query = [
            [
                '$match' => $filter
            ],
            [
                '$group' => [
                    '_id' => $tmpGroup,
                    'high' => [
                        '$max' => '$' . $priceField
                    ],
                    'low' => [
                        '$min' => '$' . $priceField
                    ],
                    'open' => [
                        '$first' => '$' . $priceField
                    ],
                    'close' => [
                        '$last' => '$' . $priceField
                    ],
                ]
            ],
            [
                '$sort' => [
                    '_id' . $dateField => $sortDirection
                ],
            ]
        ];
        if ($limitNumber !== null) {
            $query[] = [
                '$limit' => $limitNumber
            ];
        }

        $cursor = $collection->aggregate(
            $query
        );
        /* @var $cursor Cursor */
        $records = $cursor->toArray();
        /* @var $records BSONDocument[] */
        $candles = [];
        foreach ($records as $record) {
            $candles[] = new AggregateFinancialCandleDto(
                $record->high,
                $record->low,
                $record->open,
                $record->close,
                MondocTypes::dateToPHPDateTime($record->_id->{$dateField})
            );
        }

        if (!empty($candles)) {
            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if ($sortDirection === -1) {
                usort($candles, function ($first, $second) {
                    /* @var $first AggregateFinancialCandleDto */
                    /* @var $second AggregateFinancialCandleDto */
                    if ($first->getDate() < $second->getDate()) {
                        return -1;
                    }
                    return 1;
                });
            } else {
                usort($candles, function ($first, $second) {
                    /* @var $first AggregateFinancialCandleDto */
                    /* @var $second AggregateFinancialCandleDto */
                    if ($first->getDate() < $second->getDate()) {
                        return 1;
                    }
                    return -1;
                });
            }
        }

        return $candles;
    }
}
