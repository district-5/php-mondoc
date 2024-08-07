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

namespace District5\Mondoc\Db\Service\Traits;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Helper\FilterFormatter;
use District5\MondocBuilder\QueryBuilder;

/**
 * Trait CountableTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait CountableTrait
{
    /**
     * Count all documents in the collection where a $key = $value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int
     * @throws MondocConfigConfigurationException
     * @throws MondocConfigConfigurationException
     */
    public static function countWhereKeyEqualsValue(string $key, mixed $value): int
    {
        return self::countAll([
            $key => $value
        ]);
    }

    /**
     * Count all matching documents in a collection using a given filter, using given options.
     *
     * @param array $filter (optional)
     * @param array $options (optional)
     *
     * @return int
     * @throws MondocConfigConfigurationException
     */
    public static function countAll(array $filter = [], array $options = []): int
    {
        $collection = self::getCollection(
            get_called_class()
        );
        return $collection->countDocuments(
            FilterFormatter::format($filter),
            $options
        );
    }

    /**
     * Count all matching documents in a collection using a QueryBuilder
     *
     * @param QueryBuilder $builder
     *
     * @return int
     * @throws MondocConfigConfigurationException
     */
    public static function countAllByQueryBuilder(QueryBuilder $builder): int
    {
        $builder = clone $builder;
        $builder->getOptions()->setSortBy([]);
        $collection = self::getCollection(
            get_called_class()
        );
        return $collection->countDocuments(
            FilterFormatter::format(
                $builder->getArrayCopy()
            ),
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Count all matching documents in a collection using a given filter, using given options.
     *
     * @param array $options (optional)
     *
     * @return int
     * @throws MondocConfigConfigurationException
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
