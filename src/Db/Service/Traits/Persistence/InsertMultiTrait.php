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
use District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocRevisionNumberTrait;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;
use District5\Mondoc\Helper\HasTraitHelper;
use District5\Mondoc\MondocConfig;

/**
 * Trait InsertMultiTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Persistence
 */
trait InsertMultiTrait
{
    /**
     * Insert multiple models in the collection.
     *
     * @param MondocAbstractModel[] $models
     * @param array $insertOptions
     *
     * @return bool
     *
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-insertMany/
     */
    public static function insertMulti(array $models, array $insertOptions = []): bool
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
            /* @var $srv MondocAbstractService */
            $srv::insertMulti($others, $insertOptions) === true || ($otherServiceInsertionSucceeded = false);
        }

        $data = [];
        foreach ($modelsForThisService as $model) {
            $hasModified = HasTraitHelper::has($model, MondocModifiedDateTrait::class);
            $hasCreated = HasTraitHelper::has($model, MondocCreatedDateTrait::class);
            $hasRevision = $model->isRevisionNumberModel();
            /* @var $model MondocCreatedDateTrait for PhpStorm purposes only */
            if ($hasCreated === true && $model->getCreatedDate() === null) {
                $model->touchCreatedDate();
            }
            /* @var $model MondocModifiedDateTrait for PhpStorm purposes only */
            if ($hasModified === true && $model->getModifiedDate() === null) {
                $model->touchModifiedDate();
            }
            /* @var $model MondocRevisionNumberTrait for PhpStorm purposes only */
            if ($hasRevision === true && $model->getRevisionNumber() === 0) {
                $model->incrementRevisionNumber();
            }

            $asArray = $model->asArray(true);
            if ($model->hasPresetObjectId()) {
                $asArray['_id'] = $model->getPresetObjectId();
            }
            $data[] = $asArray;
        }

        if (!empty($modelsForThisService)) {
            $collection = self::getCollection(
                get_called_class()
            );
            $insert = $collection->insertMany(
                $data,
                $insertOptions
            );
            if ($insert->getInsertedCount() === count($data)) {
                $retentionModels = [];
                $ids = $insert->getInsertedIds();
                $insertedKey = 0;
                foreach ($modelsForThisService as $v) {
                    if (array_key_exists($insertedKey, $ids)) {
                        $v->setObjectId($ids[$insertedKey]);
                        $v->clearPresetObjectId();
                        $v->setMongoCollection($collection);
                        if ($v->isMondocRetentionEnabled()) {
                            $retentionModels[] = MondocRetentionService::createStub($v);
                        }
                    }
                    $insertedKey++;
                }
                MondocRetentionService::insertMulti(
                    $retentionModels
                );


                if ($otherServiceInsertionSucceeded === true) {
                    return true;
                }
            }
        }

        $result = false;
        if ($hadOtherModels) {
            $result = $otherServiceInsertionSucceeded;
        }
        return $result;
    }
}
