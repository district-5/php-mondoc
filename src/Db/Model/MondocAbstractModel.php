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

namespace District5\Mondoc\Db\Model;

use DateTime;
use District5\Mondoc\Db\Model\Traits\DirtyAttributesTrait;
use District5\Mondoc\Db\Model\Traits\MondocObjectIdTrait;
use District5\Mondoc\Db\Model\Traits\MondocMongoTypeTrait;
use District5\Mondoc\Db\Model\Traits\PresetObjectIdTrait;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Helper\MondocTypes;
use District5\Mondoc\MondocConfig;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

/**
 * Class MondocAbstractModel.
 *
 * @package District5\Mondoc\Db\Model
 */
class MondocAbstractModel extends MondocAbstractSubModel
{
    use MondocMongoTypeTrait;
    use PresetObjectIdTrait;
    use DirtyAttributesTrait;
    use MondocObjectIdTrait;

    /**
     * @var null|Collection
     */
    private ?Collection $_mondocCollection = null;

    /**
     * @var null|BSONDocument
     */
    private ?BSONDocument $_mondocBson = null;

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
    public static function inflateSingleArray(array $data): static
    {
        $done = parent::inflateSingleArray($data);
        if (array_key_exists('_id', $data)) {
            $done->setObjectId($data['_id']);
        }
        $done->inflateKeyToClassMaps();
        $done->assignDefaultVars();

        return $done;
    }

    /**
     * Assign any default variables to this model.
     */
    protected function assignDefaultVars(): void
    {
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

    /**
     * @param null|Collection $collection
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setMongoCollection(?Collection $collection)
    {
        $this->_mondocCollection = $collection;

        return $this;
    }

    /**
     * Save this model. Must be overridden in your code. Inner logic might be `return MyService::saveModel($model);`.
     *
     * @return bool
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
     */
    public function delete(): bool
    {
        if (false === $this->hasObjectId()) {
            return false;
        }

        if (null !== $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class())) {
            /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */
            return $serviceFQCN::delete($this->getObjectId());
        }

        if ($this->_mondocCollection === null) {
            return false;
        }

        try {
            $delete = $this->_mondocCollection->deleteOne(
                [
                    '_id' => $this->getObjectId()
                ]
            );

            return 1 === $delete->getDeletedCount();
        } catch (Exception) {
        }

        return false;
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
                $v = MondocTypes::phpDateToMongoDateTime($v);
            }

            if (is_array($v) && true === $this->isMondocNestedAnyType($k)) {
                $subModelClassName = $subModelClassMap[$k];
                if ($this->isMondocNestedMultipleObjects($k) === true) {
                    $subModelClassName = substr($subModelClassName, 0, -2);
                }
                if (class_exists($subModelClassName)) {
                    /* @var $subModelClassName MondocAbstractSubModel */
                    if ($this->isMondocNestedMultipleObjects($k)) {
                        $v = $subModelClassName::inflateMultipleArrays($v);
                    } else {
                        $v = $subModelClassName::inflateSingleArray($v);
                    }
                } else {
                    // Class didn't exist, so adding to unmapped.
                    $this->_mondocUnmapped[$k] = $v;
                }
            }
            $this->__set($k, $v);
            $this->addDirty($k);
        }

        return $this;
    }

    /**
     * @param bool $save
     * @return MondocAbstractModel|static|null
     */
    public function clone(bool $save = false): MondocAbstractModel|static|null
    {
        $new = clone $this;
        $new->unsetObjectId();
        $new->clearPresetObjectId();
        $new->clearDirty();
        if ($save === true) {
            if ($new->save() === true) {
                return $new;
            }

            return null;
        }
        return $new;
    }

    /**
     * @return string[]
     */
    protected function getFieldToFieldMap(): array
    {
        $this->fieldToFieldMap['_id'] = '_mondocObjectId';

        return $this->fieldToFieldMap;
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
     * Get the array of object variables to remove from the documents.
     *
     * @return array
     */
    public function getMondocKeysToRemove(): array
    {
        $allSetVariables = $this->getMondocObjectVars();
        $ignore = $this->getPropertyExclusions();
        $bsonArray = [];
        if ($this->getOriginalBsonDocument() !== null) {
            $bsonArray = array_keys($this->getOriginalBsonDocument()->getArrayCopy());
            foreach ($allSetVariables as $objVar => $objVal) {
                if (in_array($objVar, $ignore)) {
                    unset($allSetVariables[$objVar]);
                }
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
     * @return null|BSONDocument
     */
    public function getOriginalBsonDocument(): ?BSONDocument
    {
        return $this->_mondocBson;
    }

    /**
     * Decrement a field by a given delta.
     * Internally this method uses `inc` with a negative representation of `$delta`
     *
     * @param string $field
     * @param int $delta
     * @return bool
     */
    public function dec(string $field, int $delta = 1): bool
    {
        return $this->inc($field, ($delta - ($delta * 2)));
    }

    /**
     * Increment a field by a given delta.
     * Can also handle negative numbers.
     *
     * @param string $field
     * @param int $delta
     * @return bool
     */
    public function inc(string $field, int $delta = 1): bool
    {
        if (null === $service = MondocConfig::getInstance()->getServiceForModel(get_called_class())) {
            return false;
        }
        /* @var $service MondocAbstractService */
        if ($this->hasObjectId() === false) {
            $this->{$field} += $delta;
            return false;
        }

        if (true === $service::inc($this->getObjectId(), $field, $delta)) {
            $this->{$field} += $delta;
            return true;
        }
        return false;
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
            if (null !== $id = $this->toObjectId($i)) {
                $n[] = $id;
            }
        }

        return array_values($n);
    }
}
