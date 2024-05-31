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
     *
     * @return bool
     */
    public static function insert(MondocAbstractModel $model): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $data = $model->asArray();
        if (array_key_exists('_mondocObjectId', $data)) {
            unset($data['_mondocObjectId']);
        }
        if ($model->hasPresetObjectId()) {
            $data['_id'] = $model->getPresetObjectId();
        }
        $insert = $collection->insertOne(
            $data
        );
        if (1 === $insert->getInsertedCount()) {
            $model->clearPresetObjectId();
            $model->setObjectId($insert->getInsertedId());
            $model->setMongoCollection($collection);

            return true;
        }

        return false;
    }
}
