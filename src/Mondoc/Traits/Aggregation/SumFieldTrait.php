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
     * @param array $filter
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
                        '$sum' => '$' . $fieldName
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
