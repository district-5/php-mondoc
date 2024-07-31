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

namespace District5\Mondoc\Db\Service\Traits\Persistence;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\MondocBuilder\QueryBuilder;

/**
 * Trait UpdateMultiTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Persistence
 */
trait UpdateMultiTrait
{
    /**
     * Update multiple documents by applying a filter and an update query. Any references to these
     * models, held in the code are not updated.
     *
     * @param array $filter
     * @param array $update
     * @param array $updateOptions
     *
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     */
    public static function updateMany(array $filter, array $update, array $updateOptions = []): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $perform = $collection->updateMany(
            $filter,
            $update,
            $updateOptions
        );

        return $perform->isAcknowledged();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $update
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function updateManyByQueryBuilder(QueryBuilder $queryBuilder, array $update): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );

        $perform = $collection->updateMany(
            $queryBuilder->getArrayCopy(),
            $update,
            $queryBuilder->getOptions()->getArrayCopy()
        );

        return $perform->isAcknowledged();
    }
}
