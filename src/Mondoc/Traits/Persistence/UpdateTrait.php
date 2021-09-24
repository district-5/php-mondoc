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

namespace District5\Mondoc\Traits\Persistence;

use District5\Mondoc\Model\MondocAbstractModel;
use MongoDB\Collection;
use MongoDB\UpdateResult;

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
        $bsonValues = $model->getOriginalBsonDocument()->getArrayCopy();
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $bsonValues) && !in_array($k, $dirty)) {
                $changeSet[$k] = $v;
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
     * @param array $filter
     * @param array $query
     *
     * @return bool
     */
    public static function updateOne(array $filter, array $query): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        /* @var $collection Collection */
        $perform = $collection->updateOne(
            $filter,
            $query
        );
        /* @var UpdateResult */
        if (1 === $perform->getModifiedCount()) {
            return true;
        }

        return false;
    }
}
