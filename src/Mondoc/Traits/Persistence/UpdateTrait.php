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

namespace District5\Mondoc\Traits\Persistence;

use District5\Mondoc\DbModel\MondocAbstractModel;
use MongoDB\Collection;

/**
 * Trait UpdateTrait.
 *
 * @package District5\Mondoc\Traits\Persistence
 */
trait UpdateTrait
{
    /**
     * Update a model in the collection. Called automatically when using saveModel() in the AbstractService.
     *
     * @param MondocAbstractModel $model
     *
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function update($model): bool
    {
        if (!is_object($model) || false === method_exists($model, 'isMondocModel')) {
            return false;
        }
        if (empty($model->getDirty())) {
            return true;
        }
        $data = $model->asArray();
        unset($data['_id']);
        $dirty = $model->getDirty();
        $changeSet = [];
        foreach ($dirty as $key) {
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
        if (array_key_exists('_mondocMongoId', $changeSet)) {
            unset($changeSet['_mondocMongoId']);
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
            ['_id' => $model->getMongoId()],
            $query
        );
        if ($performed->isAcknowledged()) {
            $model->clearDirty();
            $model->setMongoCollection($collection);

            return true;
        }

        return false;
    }

    /**
     * Update a single document by applying a filter and an update query. Any references to
     * this model, held in the code are not updated.
     *
     * @param array $filter
     * @param array $query
     *
     * @return bool
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
    public static function updateOne(array $filter, array $query): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $perform = $collection->updateOne(
            $filter,
            $query
        );

        if (1 === $perform->getModifiedCount()) {
            return true;
        }

        return false;
    }
}
