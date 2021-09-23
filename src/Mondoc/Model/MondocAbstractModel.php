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

namespace District5\Mondoc\Model;

use DateTime;
use District5\Mondoc\Helper\MondocMongoTypeConverter;
use District5\Mondoc\Model\Traits\DirtyAttributesTrait;
use District5\Mondoc\Model\Traits\MondocMongoIdTrait;
use District5\Mondoc\Model\Traits\MondocMongoTypeTrait;
use District5\Mondoc\Model\Traits\PresetMongoIdTrait;
use District5\Mondoc\MondocConfig;
use District5\Mondoc\Service\MondocAbstractService;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

/**
 * Class MondocAbstractModel.
 *
 * @package District5\Mondoc\Model
 */
class MondocAbstractModel extends MondocAbstractSubModel
{
    use MondocMongoTypeTrait;
    use PresetMongoIdTrait;
    use DirtyAttributesTrait;
    use MondocMongoIdTrait;

    /**
     * @var null|Collection
     */
    private $_mongoCollection;

    /**
     * @var null|BSONDocument
     */
    private $_mondocBson;

    /**
     * @param null|Collection $collection
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setMongoCollection(?Collection $collection)
    {
        $this->_mongoCollection = $collection;

        return $this;
    }

    /**
     * Save this model. Must be overridden in your code. Inner logic might be `return MyService::saveModel($model);`.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function save(): bool
    {
        if (null !== $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class())) {
            /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */
            return $serviceFQCN::saveModel($this);
        }
        return false;
    }

    /**
     * Delete this model. Must be overridden in your code. Inner logic might be `return MyService::deleteModel($model);`.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function delete(): bool
    {
        if (false === $this->hasMongoId()) {
            return false;
        }

        if (null !== $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class())) {
            /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */
            return $serviceFQCN::delete($this->getMongoId());
        }

        try {
            $delete = $this->_mongoCollection->deleteOne(
                [
                    '_id' => $this->getMongoId()
                ]
            );

            return 1 === $delete->getDeletedCount();
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * Set the ObjectId for this model. Primarily used by the service.
     *
     * @param ObjectId $objectId
     *
     * @return $this
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    final public function setMongoId(ObjectId $objectId)
    {
        $this->_mondocMongoId = $objectId;

        return $this;
    }

    /**
     * Inflate a model from an array of data.
     *
     * @param array $data
     *
     * @return $this
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection DuplicatedCode
     */
    public function inflateFurtherValues(array $data)
    {
        $subModelClassMap = $this->getKeyToClassMap();
        $fieldMap = $this->getFieldToFieldMap();

        foreach ($data as $k => $v) {
            if (is_int($k)) {
                continue;
            }

            if (in_array($k, $this->getPropertyExclusions())) {
                continue;
            }
            if (array_key_exists($k, $fieldMap)) {
                $k = $fieldMap[$k];
            }
            if ($v instanceof DateTime) {
                $v = MondocMongoTypeConverter::phpDateToMongoDateTime($v);
            }

            if (is_array($v) && array_key_exists($k, $subModelClassMap)) {
                $subModelClassName = $subModelClassMap[$k];
                $isMulti = false;
                if ('[]' === substr($subModelClassName, -2)) {
                    $isMulti = true;
                    $subModelClassName = substr($subModelClassName, 0, -2);
                }
                if (class_exists($subModelClassName)) {
                    /* @var $subModelClassName MondocAbstractSubModel */
                    if ($isMulti) {
                        $v = $subModelClassName::inflateMultipleArrays($v);
                    } else {
                        $v = $subModelClassName::inflateSingleArray($v);
                        if (null === $v) {
                            continue;
                        }
                    }
                } else {
                    // Class didn't exist, so adding to unmapped.
                    $this->unmappedFields[$k] = $v;
                }
            }
            $this->__set($k, $v);
            $this->addDirty($k, $v);
        }

        return $this;
    }

    /**
     * @param BSONDocument $document
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function inflateSingleBsonDocument(BSONDocument $document)
    {
        $m = self::inflateSingleArray(
            $document->getArrayCopy()
        );
        $m->setOriginalBsonDocument($document);

        return $m;
    }

    /**
     * Inflate an array of data into the called model.
     *
     * @param array $data
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function inflateSingleArray(array $data)
    {
        $done = parent::inflateSingleArray($data);
        if ($done) {
            if (array_key_exists('_id', $data)) {
                $done->setMongoId($data['_id']);
            }
            $done->inflateKeyToClassMaps();
            $done->assignDefaultVars();
        }

        return $done;
    }

    /**
     * Inflate multiple arrays of data into the called model.
     *
     * @param array $data
     *
     * @return $this[]
     */
    public static function inflateMultipleArrays(array $data): array
    {
        $done = parent::inflateMultipleArrays($data);
        $new = [];
        $i = 0;
        foreach ($done as $d) {
            $d->inflateKeyToClassMaps();
            $d->assignDefaultVars();
            $new[] = $d;
            ++$i;
        }

        return $new;
    }

    /**
     * @return bool
     */
    final public function isMondocModel(): bool
    {
        return true;
    }

    /**
     * @return null|BSONDocument
     */
    public function getOriginalBsonDocument(): ?BSONDocument
    {
        return $this->_mondocBson;
    }

    /**
     * Get the array of object variables to remove from the documents.
     *
     * @return array
     */
    public function getMondocKeysToRemove(): array
    {
        $allSetVariables = $this->getMondocObjectVars();
        $ignore = $this->getPropertyExclusions();
        $bsonArray = array_keys($this->getOriginalBsonDocument()->getArrayCopy());
        foreach ($allSetVariables as $objVar => $objVal) {
            if (in_array($objVar, $ignore)) {
                unset($allSetVariables[$objVar]);

                continue;
            }
        }
        $allSetVariables = array_keys($allSetVariables);

        $toUnset = [];
        foreach ($bsonArray as $bsonKey) {
            if ('_id' !== $bsonKey && !in_array($bsonKey, $allSetVariables)) {
                $toUnset[$bsonKey] = 1;
            }
        }

        return $toUnset;
    }

    /**
     * Given an array of ObjectIds, this will ensure they are indeed ObjectIds.
     *
     * @param array $data
     *
     * @return ObjectId[]
     * @noinspection PhpUnused
     */
    protected function convertArrayOfMongoIdsToMongoIds(array $data): array
    {
        $n = [];
        foreach ($data as $i) {
            if (null !== $id = $this->convertToMongoId($i)) {
                $n[] = $id;
            }
        }

        return array_values($n);
    }

    /**
     * Assign any default variables to this model.
     */
    protected function assignDefaultVars()
    {
    }

    /**
     * @return string[]
     */
    protected function getFieldToFieldMap(): array
    {
        $this->fieldToFieldMap['_id'] = '_mondocMongoId';

        return $this->fieldToFieldMap;
    }

    /**
     * @param BSONDocument $document
     *
     * @return $this
     */
    private function setOriginalBsonDocument(BSONDocument $document): MondocAbstractModel
    {
        $this->_mondocBson = $document;

        return $this;
    }
}
