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

use District5\Mondoc\Db\Model\Traits\DirtyAttributesTrait;
use District5\Mondoc\Db\Model\Traits\MondocMongoTypeTrait;
use District5\Mondoc\Db\Model\Traits\MondocObjectIdTrait;
use District5\Mondoc\Db\Model\Traits\PresetObjectIdTrait;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\MondocConfig;
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
     */
    public static function inflateSingleBsonDocument(BSONDocument $document): static
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
     */
    public static function inflateSingleArray(array $data): static
    {
        $done = parent::inflateSingleArray($data);
        if (array_key_exists('_id', $data)) {
            $done->setObjectId($data['_id']);
        }
        $done->assignDefaultVars();

        return $done;
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
     */
    public function setMongoCollection(?Collection $collection): static
    {
        $this->_mondocCollection = $collection;

        return $this;
    }

    /**
     * Save this model. Must be overridden in your code. Inner logic might be `return MyService::saveModel($model);`.
     *
     * @param array $insertOrUpdateOptions
     *
     * @return bool
     *
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-insertOne/
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     */
    public function save(array $insertOrUpdateOptions = []): bool
    {
        if (null !== $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class())) {
            /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */
            return $serviceFQCN::saveModel($this, $insertOrUpdateOptions);
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

        $delete = $this->_mondocCollection->deleteOne([
            '_id' => $this->getObjectId()
        ]);

        return 1 === $delete->getDeletedCount();
    }

    /**
     * @return bool
     */
    final public function isMondocModel(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    final public function isMondocSubModel(): bool
    {
        return false;
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
        $fieldMapKey = array_keys($this->getFieldAliasMap());
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
            if ('_id' !== $bsonKey && !in_array($bsonKey, $allSetVariables) && !in_array($bsonKey, $fieldMapKey)) {
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

        $perform = $service::inc($this->getObjectId(), $field, $delta);
        if ($perform === true) {
            $this->{$field} += $delta;
        }

        return $perform;
    }

    /**
     * Given an array of ObjectIds, this will ensure they are indeed ObjectIds.
     *
     * @param array $data
     *
     * @return ObjectId[]
     */
    protected function convertArrayOfMongoIdsToMongoIds(array $data): array
    {
        $n = [];
        foreach ($data as $i) {
            if (null !== $id = self::toObjectId($i)) {
                $n[] = $id;
            }
        }

        return array_values($n);
    }

    /**
     * Export the model as a JSON encodable array, omitting certain fields.
     *
     * @param array $omitKeys (optional) fields to omit.
     * @return array
     */
    public function asJsonEncodableArray(array $omitKeys = []): array
    {
        $data = parent::asJsonEncodableArray();
        $data['_id'] = $this->getObjectIdString();
        foreach ($omitKeys as $o) {
            unset($data[$o]);
        }

        return $data;
    }
}
