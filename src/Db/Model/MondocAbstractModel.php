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

use District5\Mondoc\Db\Model\Traits\MondocRetentionTrait;
use District5\Mondoc\Db\Model\Traits\Static\DirtyAttributesTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocAtomicOperationsTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocBsonDocumentTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocCollectionTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocMongoTypeTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocObjectIdTrait;
use District5\Mondoc\Db\Model\Traits\Static\PresetObjectIdTrait;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\HasTraitHelper;
use District5\Mondoc\MondocConfig;
use MongoDB\BSON\ObjectId;
use MongoDB\Model\BSONDocument;

/**
 * Class MondocAbstractModel.
 *
 * @package District5\Mondoc\Db\Model
 */
class MondocAbstractModel extends MondocAbstractSubModel
{
    use MondocObjectIdTrait;
    use PresetObjectIdTrait;
    use MondocCollectionTrait;
    use MondocBsonDocumentTrait;
    use MondocMongoTypeTrait;
    use MondocAtomicOperationsTrait;
    use DirtyAttributesTrait;

    /**
     * @param BSONDocument $document
     *
     * @return $this
     * @throws MondocConfigConfigurationException
     * @throws MondocException
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
     * @throws MondocConfigConfigurationException
     * @throws \District5\MondocEncryption\Exception\MondocEncryptionException
     * @throws MondocException
     * @noinspection PhpFullyQualifiedNameUsageInspection
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
     * Save this model. Must be overridden in your code. Inner logic might be `return MyService::saveModel($model);`.
     *
     * @param array $insertOrUpdateOptions
     *
     * @return bool
     *
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-insertOne/
     */
    public function save(array $insertOrUpdateOptions = []): bool
    {
        $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class());
        /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */

        return $serviceFQCN::saveModel(
            $this,
            $insertOrUpdateOptions
        );
    }

    /**
     * Delete this model. Must be overridden in your code. Inner logic might be `return MyService::deleteModel($model);`.
     *
     * @return bool
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function delete(): bool
    {
        if (false === $this->hasObjectId()) {
            return false;
        }

        $serviceFQCN = MondocConfig::getInstance()->getServiceForModel(get_called_class());
        /* @var $serviceFQCN MondocAbstractService (it's not, it's actually a string) */

        return $serviceFQCN::deleteModel(
            $this
        );
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
        $fieldMapKey = array_keys($this->getFieldAliasMap());
        $bsonArray = [];
        if ($this->getOriginalBsonDocument() !== null) {
            $bsonArray = array_keys($this->getOriginalBsonDocument()->getArrayCopy());
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
     * @return bool
     */
    public function isMondocRetentionEnabled(): bool
    {
        return HasTraitHelper::has($this, MondocRetentionTrait::class);
    }

    /**
     * Given an array of ObjectIds, this will ensure they are indeed ObjectIds.
     *
     * @param array $data
     *
     * @return ObjectId[]
     * @noinspection PhpUnused
     * @deprecated use `arrayOfObjectIdsToObjectIds()` instead.
     * @see \District5\Mondoc\Helper\Traits\ObjectIdConversionTrait::arrayOfObjectIdsToObjectIds()
     */
    protected function convertArrayOfMongoIdsToMongoIds(array $data): array
    {
        return self::arrayOfObjectIdsToObjectIds($data);
    }

    /**
     * Export the model as a JSON encodable array, optionally omitting certain fields.
     *
     * @param array $omitKeys (optional) fields to omit.
     * @return array
     * @throws MondocConfigConfigurationException
     * @throws MondocException
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

    /**
     * @return string
     * @throws MondocServiceMapErrorException
     */
    public static function getMondocServiceClass(): string
    {
        return MondocConfig::getInstance()->getServiceForModel(
            get_called_class()
        );
    }
}
