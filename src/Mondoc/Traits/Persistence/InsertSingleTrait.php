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

/**
 * Trait InsertSingleTrait.
 *
 * @package District5\Mondoc\Traits\Persistence
 */
trait InsertSingleTrait
{
    /**
     * Insert a model in the collection. Called automatically when using saveModel() in the AbstractService.
     *
     * @param MondocAbstractModel $model
     *
     * @return bool
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function insert($model): bool
    {
        if (!is_object($model) || false === method_exists($model, 'isMondocModel')) {
            return false;
        }
        $collection = self::getCollection(
            get_called_class()
        );
        $data = $model->asArray();
        if (array_key_exists('_mondocMongoId', $data)) {
            unset($data['_mondocMongoId']);
        }
        if ($model->hasPresetMongoId()) {
            $data['_id'] = $model->getPresetMongoId();
        }
        // @var $collection Collection
        $insert = $collection->insertOne(
            $data
        );
        if (1 === $insert->getInsertedCount()) {
            $model->clearPresetMongoId();
            $model->setMongoId($insert->getInsertedId());
            $model->setMongoCollection($collection);

            return true;
        }

        return false;
    }
}
