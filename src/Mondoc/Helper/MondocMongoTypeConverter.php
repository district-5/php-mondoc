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

namespace District5\Mondoc\Helper;

use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Class MondocMongoTypeConverter.
 *
 * @package District5\Mondoc\Helper
 */
class MondocMongoTypeConverter
{
    /**
     * Convert a Mongo UTCDateTime to a PHP DateTime. It doesn't matter which you pass in.
     *
     * @param DateTime|UTCDateTime $provided
     *
     * @return null|DateTime
     */
    public static function dateToPHPDateTime($provided): ?DateTime
    {
        if (!is_object($provided)) {
            return null;
        }
        if ($provided instanceof DateTime) {
            return $provided;
        }
        if ($provided instanceof UTCDateTime) {
            return $provided->toDateTime();
        }

        return null;
    }

    /**
     * Convert a PHP DateTime to a Mongo UTCDateTime. It doesn't matter which you pass in.
     *
     * @param DateTime|UTCDateTime $provided
     *
     * @return null|UTCDateTime
     */
    public static function phpDateToMongoDateTime($provided): ?UTCDateTime
    {
        if (!is_object($provided)) {
            return null;
        }
        if ($provided instanceof UTCDateTime) {
            return $provided;
        }
        if ($provided instanceof DateTime) {
            return new UTCDateTime(($provided->format('Uv')));
        }

        return null;
    }

    /**
     * Lazy convert BSONDocuments and BSONArray's to php arrays.
     *
     * @param array|BSONArray|BSONDocument $item
     *
     * @return array
     */
    public static function arrayToPhp($item)
    {
        if (is_array($item)) {
            return $item;
        }
        if ($item instanceof BSONDocument || $item instanceof BSONArray) {
            return $item->getArrayCopy();
        }

        return $item;
    }

    /**
     * Convert an ObjectId to a MongoId.
     *
     * @param null|array|ObjectId|string $id
     *
     * @return null|ObjectId
     */
    public static function convertToMongoId($id): ?ObjectId
    {
        /* @var $id array|null|string|ObjectId */
        if (null === $id) {
            return $id;
        }
        if (is_object($id) && $id instanceof ObjectId) {
            return $id;
        }
        // if (is_array($id)) {
        //     if (array_key_exists('oid', $id)) {
        //         return new ObjectId($id['oid']);
        //     }
        //     if (array_key_exists('$oid', $id)) {
        //         return new ObjectId($id['$oid']);
        //     }
        // }
        if (is_string($id) && 24 === strlen($id)) {
            return new ObjectId($id);
        }

        return null;
    }
}
