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
use District5\Mondoc\MondocConfig;

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
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function insertMulti(array $models): bool
    {
        if (empty($models)) {
            return true;
        }

        $configInstance = MondocConfig::getInstance();
        $serviceName = get_called_class();
        $modelsForThisService = [];
        $otherServiceModels = [];
        foreach ($models as $k => $model) {
            $modelService = $configInstance->getServiceForModel(get_class($model));
            if ($modelService !== $serviceName) {
                // This model belongs to a different service.
                if (!array_key_exists($modelService, $otherServiceModels)) {
                    $otherServiceModels[$modelService] = [];
                }

                $otherServiceModels[$modelService][$k] = $model;
            } else {
                $modelsForThisService[$k] = $model;
            }
        }

        $hadOtherModels = false;
        $otherServiceInsertionSucceeded = true;
        foreach ($otherServiceModels as $srv => $others) {
            $hadOtherModels = true;
            /* @var $srv \District5\Mondoc\DbService\MondocAbstractService */
            if ($srv::insertMulti($others) !== true) {
                $otherServiceInsertionSucceeded = false;
            }
        }

        $data = [];
        foreach ($modelsForThisService as $model) {
            $asArray = $model->asArray();
            if (array_key_exists('_mondocMongoId', $asArray)) {
                unset($asArray['_mondocMongoId']);
            }
            if ($model->hasPresetMongoId()) {
                $asArray['_id'] = $model->getPresetMongoId();
            }
            $data[] = $asArray;
        }

        if (!empty($modelsForThisService)) {
            $collection = self::getCollection(
                get_called_class()
            );
            $insert = $collection->insertMany(
                $data
            );
            if ($insert->getInsertedCount() === count($data)) {
                $ids = $insert->getInsertedIds();
                $insertedKey = 0;
                foreach ($modelsForThisService as $v) {
                    if (array_key_exists($insertedKey, $ids)) {
                        $v->setMongoId($ids[$insertedKey]);
                        $v->clearPresetMongoId();
                        $v->setMongoCollection($collection);
                    }
                    $insertedKey++;
                }

                if ($otherServiceInsertionSucceeded === true) {
                    return true;
                }
            }
        }

        if ($hadOtherModels) {
            return $otherServiceInsertionSucceeded;
        }

        return false;
    }
}
