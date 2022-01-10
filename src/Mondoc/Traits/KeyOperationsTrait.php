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

/**
 * Trait KeyOperationsTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait KeyOperationsTrait
{
    /**
     * Remove a key from a single document.
     *
     * @param string $key
     * @param MondocAbstractModel $model
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public static function removeKey(string $key, MondocAbstractModel $model): bool
    {
        $fields = $model->getUnmappedFields();
        if (!array_key_exists($key, $fields)) {
            return false;
        }
        $collection = self::getCollection(
            get_called_class()
        );
        $result = $collection->updateOne(
            ['_id' => $model->getMongoId()],
            ['$unset' => [$key => 1]]
        );

        return 1 === $result->getModifiedCount();
    }
}
