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

use District5\Mondoc\Helper\MondocMongoTypeConverter;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

/**
 * Trait DeleteSingleTrait.
 *
 * @package District5\Mondoc\Traits\Deletion
 */
trait DeleteSingleTrait
{
    /**
     * Delete a single document from the collection by a given ID.
     *
     * @param ObjectId|string $id
     *
     * @return bool
     */
    public static function delete($id): bool
    {
        $id = MondocMongoTypeConverter::convertToMongoId($id);
        $collection = self::getCollection(
            get_called_class()
        );
        // @var $collection Collection
        return $collection->deleteOne(
            ['_id' => $id]
        )->isAcknowledged();
    }
}
