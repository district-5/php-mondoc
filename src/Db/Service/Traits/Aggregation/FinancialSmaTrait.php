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

use DateTime;
use District5\Mondoc\Dto\AggregateSmaDto;
use District5\Mondoc\Helper\MondocTypes;
use Exception;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

/**
 * Trait FinancialSmaTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Aggregation
 */
trait FinancialSmaTrait
{
    /**
     * Get the simple moving average for a given period.
     *
     * @param string $symbolField The field to use for the symbol.
     * @param string $symbolValue The symbol to get the SMA for.
     * @param DateTime $from Date to start from
     * @param DateTime $to Date to end at
     * @param string $dateField
     * @param string $priceField
     * @param int $numGroupMinutes The number of minutes to group by.
     * @param int $smaNumPeriods The number of periods to calculate the SMA for. 7 means average of 7 periods.
     * @param int $sortDirection (optional) default 1. 1 = ascending, -1 = descending
     * @param array $additionalFilter (optional) default []. additional filter to apply to the aggregation
     * @return AggregateSmaDto[]
     *
     * @throws Exception
     * @example $this->getFinancialSma(
     *      $symbolField = 'pair',
     *      $symbolValue = 'ETH-USD',
     *      $from = new DateTime('2020-01-01 00:00:00'),
     *      $to = new DateTime('2020-01-07 23:59:59'),
     *      $dateField = 'createdDate',
     *      $priceField = 'price'
     *      $numGroupMinutes = 5, // 5 minute granularity
     *      $smaNumPeriods = 7, // 7 periods
     *      $sortDirection = 1, // 1 = ascending, -1 = descending
     *      $additionalFilter = ['platform' => 'binance']
     * );
     */
    public function getFinancialSma(string $symbolField, string $symbolValue, DateTime $from, DateTime $to, string $dateField, string $priceField, int $numGroupMinutes, int $smaNumPeriods, int $sortDirection = 1, array $additionalFilter = []): array
    {
        $match = [
            '$match' => [
                $symbolField => $symbolValue,
                $dateField => [
                    '$gte' => MondocTypes::phpDateToMongoDateTime($from),
                    '$lte' => MondocTypes::phpDateToMongoDateTime($to),
                ],
            ]
        ];

        if (!empty($additionalFilter)) {
            $match['$match'] = array_merge($match['$match'], $additionalFilter);
        }

        $group = [
            '$group' => [
                '_id' => [
                    $symbolField => '$' . $symbolField,
                    'time' => [
                        '$dateTrunc' => [
                            'unit' => 'minute',
                            'date' => '$' . $dateField,
                            'binSize' => $numGroupMinutes,
                        ],
                    ],
                ],
                // 'high' => [
                //     '$max' => '$' . $priceField,
                // ],
                // 'low' => [
                //     '$min' => '$' . $priceField,
                // ],
                // 'open' => [
                //     '$first' => '$' . $priceField,
                // ],
                'close' => [
                    '$last' => '$' . $priceField,
                ],
            ]
        ];

        $sort = [
            '$sort' => [
                '_id.time' => $sortDirection,
            ]
        ];

        $project = [
            '$project' => [
                '_id' => 1,
                'price' => '$close',
                $dateField => [
                    '$dateToString' => [
                        //'format' => '%Y-%m-%d %H:%M:%S',
                        'date' => '$_id.time',
                    ],
                ],
            ],
        ];

        $windowFields = [
            '$setWindowFields' => [
                'partitionBy' => '_id.' . $symbolField,
                'sortBy' => [
                    '_id.time' => $sortDirection,
                ],
                'output' => [
                    'sma' => [
                        '$avg' => '$price',
                        'window' => [
                            'documents' => [(0-$smaNumPeriods), 0]
                        ]
                    ]
                ],
            ]
        ];

        $cursor = $this->service::getCollection(
            $this->service
        )->aggregate(
            [
                $match,
                $group,
                $sort,
                $project,
                $windowFields,
            ]
        );
        /* @var $cursor Cursor */

        $records = $cursor->toArray();
        /* @var $records BSONDocument[] */

        $dataObjects = [];
        foreach ($records as $record) {
            $dataObjects[] = new AggregateSmaDto(
                $record['price'],
                $record['sma'],
                new DateTime($record[$dateField])
            );
        }

        return $dataObjects;
    }
}
