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

use District5\Mondoc\Model\MondocAbstractModel;

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
