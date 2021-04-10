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
 * Trait InsertMultiTrait.
 *
 * @package District5\Mondoc\Traits\Persistence
 */
trait InsertMultiTrait
{
    /**
     * Insert multiple models in the collection.
     *
     * @param MondocAbstractModel[] $models
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public static function insertMulti(array $models): bool
    {
        if (empty($models)) {
            return true;
        }
        $data = [];
        foreach ($models as $model) {
            $asArray = $model->asArray();
            if (array_key_exists('_mondocMongoId', $asArray)) {
                unset($asArray['_mondocMongoId']);
            }
            if ($model->hasPresetMongoId()) {
                $asArray['_id'] = $model->getPresetMongoId();
            }
            $data[] = $asArray;
        }
        $collection = self::getCollection(
            get_called_class()
        );
        /* @var $collection Collection */
        $insert = $collection->insertMany(
            $data
        );
        if ($insert->getInsertedCount() === count($data)) {
            $ids = $insert->getInsertedIds();
            foreach ($ids as $k => $id) {
                $models[$k]->setMongoId($id);
                $models[$k]->clearPresetMongoId();
                $models[$k]->setMongoCollection($collection);
            }

            return true;
        }

        return false;
    }
}
