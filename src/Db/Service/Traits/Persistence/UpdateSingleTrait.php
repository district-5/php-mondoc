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

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;
use District5\Mondoc\Helper\FilterFormatter;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\Collection;

/**
 * Trait UpdateSingleTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Persistence
 */
trait UpdateSingleTrait
{
    /**
     * Update a model in the collection. Called automatically when using saveModel() in the AbstractService.
     *
     * @param MondocAbstractModel $model
     * @param array $updateOptions
     *
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     */
    public static function update(MondocAbstractModel $model, array $updateOptions = []): bool
    {
        if (empty($model->getDirty())) {
            return true;
        }
        $data = $model->asArray();
        unset($data['_id']);
        $dirty = $model->getDirty();
        if (empty($dirty)) {
            return true;
        }
        $changeSet = [];
        foreach ($dirty as $key) {
            $key = $model->getFieldAliasSingleMap($key, true);
            if (array_key_exists($key, $data)) {
                $changeSet[$key] = $data[$key];
            } else {
                $changeSet[$key] = null;
            }
        }
        if ($model->getOriginalBsonDocument() !== null) {
            $bsonValues = $model->getOriginalBsonDocument()->getArrayCopy();
            foreach ($data as $k => $v) {
                if (!array_key_exists($k, $bsonValues) && !in_array($k, $dirty)) {
                    $changeSet[$k] = $v;
                }
            }
        }
        $unsetValues = $model->getMondocKeysToRemove();

        $collection = self::getCollection(
            get_called_class()
        );
        /* @var $collection Collection */
        $query = ['$set' => $changeSet];
        if (!empty($unsetValues)) {
            $query['$unset'] = $unsetValues;
        }
        $performed = $collection->updateOne(
            ['_id' => $model->getObjectId()],
            FilterFormatter::format($query),
            $updateOptions
        );
        if ($performed->isAcknowledged() && $performed->getMatchedCount() === 1) {
            $model->clearDirty();
            $model->setMongoCollection($collection);

            if ($model->isMondocRetentionEnabled()) {
                MondocRetentionService::create($model);
            }

            return true;
        }

        return false;
    }

    /**
     * Update a single document by applying a filter and an update query. Any references to
     * this model, held in the code are not updated.
     *
     * @param array $filter
     * @param array $update
     * @param array $updateOptions
     *
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     * @example
     *     MyService::updateOne(
     *          [
     *              '_id' => new ObjectId()
     *          ],
     *          [
     *              '$set' => ['age' => 2]
     *          ]
     *      );
     *
     */
    public static function updateOne(array $filter, array $update, array $updateOptions = []): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );

        $perform = $collection->updateOne(
            FilterFormatter::format($filter),
            FilterFormatter::format($update),
            $updateOptions
        );

        return $perform->isAcknowledged();
    }

    /**
     * Update a single document by using the QueryBuilder for both the filter and options, and
     * specifying the update query to use. Any references to this model, held in the code are
     * not updated.
     *
     * @param QueryBuilder $queryBuilder
     * @param array $update
     *
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function updateOneByQueryBuilder(QueryBuilder $queryBuilder, array $update): bool
    {
        return self::updateOne(
            $queryBuilder->getArrayCopy(),
            $update,
            $queryBuilder->getOptions()->getArrayCopy()
        );
    }
}
