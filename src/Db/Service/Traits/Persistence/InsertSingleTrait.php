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
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;
use MongoDB\Model\BSONDocument;

/**
 * Trait InsertSingleTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Persistence
 */
trait InsertSingleTrait
{
    /**
     * Insert a model in the collection. Called automatically when using saveModel() in the AbstractService.
     *
     * @param MondocAbstractModel $model
     * @param array $insertOptions
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-insertOne/
     */
    public static function insert(MondocAbstractModel $model, array $insertOptions = []): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $data = $model->asArray(true);
        if ($model->hasPresetObjectId()) {
            $data['_id'] = $model->getPresetObjectId();
        }
        $insert = $collection->insertOne(
            $data,
            $insertOptions
        );

        $success = $insert->getInsertedCount() === 1;
        if ($success === true) {
            $model->clearPresetObjectId();
            $model->setObjectId($insert->getInsertedId());
            $model->setMongoCollection($collection);
            $model->clearDirty();
            $data['_id'] = $model->getObjectId();
            $model->setOriginalBsonDocument(new BSONDocument($data));

            if ($model->isMondocRetentionEnabled()) {
                MondocRetentionService::create($model);
            }
        }

        return $success;
    }
}
