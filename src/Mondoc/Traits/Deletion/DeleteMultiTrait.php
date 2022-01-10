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

namespace District5\Mondoc\Traits\Deletion;

use MongoDB\DeleteResult;

/**
 * Trait DeleteMultiTrait.
 *
 * @package District5\Mondoc\Traits\Deletion
 */
trait DeleteMultiTrait
{
    /**
     * Delete multiple documents based on a given query and options.
     *
     * @param array $query
     * @param array $options (optional)
     *
     * @return null|int
     * @noinspection PhpUnused
     */
    public static function deleteMulti(array $query, array $options = []): ?int
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $delete = $collection->deleteMany(
            $query,
            $options
        );
        if ($delete instanceof DeleteResult) {
            return $delete->getDeletedCount();
        }

        return null;
    }
}
