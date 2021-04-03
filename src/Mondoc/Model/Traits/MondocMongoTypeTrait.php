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

namespace District5\Mondoc\Model\Traits;

use DateTime;
use District5\Mondoc\Helper\MondocMongoTypeConverter;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Trait MondocMongoTypeTrait.
 *
 * @package District5\Mondoc\Model\Traits
 */
trait MondocMongoTypeTrait
{
    /**
     * Convert a date object to either a PHP DateTime (by passing $asMongo=false)
     * or as a Mongo UTCDateTime (by passing $asMongo=true).
     *
     * @param DateTime|UTCDateTime $date
     * @param bool                 $asMongo
     *
     * @return null|DateTime|UTCDateTime
     * @noinspection PhpUnused
     */
    protected function convertDateObject($date, $asMongo = false)
    {
        if (is_array($date) && array_key_exists('$date', $date)) {
            $date = new UTCDateTime($date['$date']['$numberLong']);
        } elseif (is_array($date) && array_key_exists('milliseconds', $date)) {
            $date = new UTCDateTime($date['milliseconds']);
        }
        if ($asMongo) {
            return MondocMongoTypeConverter::phpDateToMongoDateTime($date);
        }

        return MondocMongoTypeConverter::dateToPHPDateTime($date);
    }

    /**
     * Convert any array type to a PHP Array.
     *
     * @param array|BSONArray $provided
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function arrayToPhpArray($provided)
    {
        if (!is_object($provided)) {
            if (!is_array($provided)) {
                return [];
            }

            return $provided;
        }
        if ($provided instanceof BSONArray) {
            $provided = $provided->getArrayCopy();
        }
        if ($provided instanceof BSONDocument) {
            $provided = $provided->getArrayCopy();
        }

        return $provided;
    }

    /**
     * Convert an ObjectId to a MongoId.
     *
     * @param null|array|ObjectId|string $id
     *
     * @return null|ObjectId
     */
    protected function convertToMongoId($id): ?ObjectId
    {
        return MondocMongoTypeConverter::convertToMongoId($id);
    }

    /**
     * Iterate through an array of ObjectIds to deduplicate them.
     *
     * @param ObjectId[] $ids
     *
     * @return ObjectId[]
     * @noinspection PhpUnused
     */
    protected function deduplicateArrayOfMongoIds(array $ids): array
    {
        $tmp = [];
        $final = [];
        foreach ($ids as $id) {
            $newId = $this->convertToMongoId($id);
            if (null !== $newId && !in_array($newId->__toString(), $tmp)) {
                $final[] = $newId;
                $tmp[] = $newId->__toString();
            }
        }

        return $final;
    }
}
