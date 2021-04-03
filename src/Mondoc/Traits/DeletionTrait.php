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

namespace District5\Mondoc\Traits;

use District5\Mondoc\Model\MondocAbstractModel;
use District5\Mondoc\Traits\Deletion\DeleteMultiTrait;
use District5\Mondoc\Traits\Deletion\DeleteSingleTrait;

/**
 * Trait DeletionTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait DeletionTrait
{
    use DeleteSingleTrait;
    use DeleteMultiTrait;

    /**
     * Delete a model from the collection.
     *
     * @param MondocAbstractModel $model
     *
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function deleteModel($model): bool
    {
        if (!is_object($model) || false === method_exists($model, 'isMondocModel')) {
            return false;
        }
        if (self::delete($model->getMongoId())) {
            $model->setMongoCollection(null);
            $model->unsetMongoId();

            return true;
        }

        return false;
    }
}
