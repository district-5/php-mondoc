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

namespace District5\Mondoc\Db\Service\Traits;

use District5\Date\Date;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\InsertMultiTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\InsertSingleTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\UpdateTrait;

/**
 * Trait PersistenceTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait PersistenceTrait
{
    use InsertSingleTrait;
    use InsertMultiTrait;
    use UpdateTrait;

    /**
     * Save a model into the collection.
     *
     * @param MondocAbstractModel $model
     *
     * @return bool
     */
    public static function saveModel(MondocAbstractModel $model): bool
    {
        if (!is_object($model) || false === method_exists($model, 'isMondocModel')) {
            return false;
        }
        $uses = class_uses($model);
        $hasModified = in_array(MondocModifiedDateTrait::class, $uses, true);
        $hasCreated = in_array(MondocCreatedDateTrait::class, $uses, true);
        if (null === $model->getObjectId()) {
            /* @var $model MondocCreatedDateTrait */
            if ($hasCreated === true && $model->getCreatedDate(false) === null) {
                $model->{'setCreatedDate'}(Date::nowUtc());
            }
            /* @var $model MondocModifiedDateTrait */
            if ($hasModified === true && $model->getModifiedDate(false) === null) {
                $model->{'setModifiedDate'}(Date::nowUtc());
            }
            return self::insert($model);
        }

        if ($hasModified === true && !in_array('md', $model->getDirty())) {
            $model->{'setModifiedDate'}(Date::nowUtc());
        }
        return self::update($model);
    }
}
