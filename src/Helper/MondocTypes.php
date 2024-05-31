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

namespace District5\Mondoc\Helper;

use DateTime;
use District5\Date\Date;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use stdClass;

/**
 * Class MondocTypes.
 *
 * @package District5\Mondoc\Helper
 */
class MondocTypes
{
    /**
     * Convert a Mongo UTCDateTime to a PHP DateTime. It doesn't matter which you pass in.
     *
     * @param DateTime|UTCDateTime $provided
     *
     * @return null|DateTime
     */
    public static function dateToPHPDateTime(mixed $provided): ?DateTime
    {
        if ($provided instanceof UTCDateTime) {
            if (false !== $converted = Date::mongo()->convertFrom($provided)) {
                return $converted;
            }
            return null;
        }
        if ($provided instanceof DateTime) {
            return $provided;
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
    public static function phpDateToMongoDateTime(mixed $provided): ?UTCDateTime
    {
        if (!is_object($provided)) {
            return null;
        }
        if ($provided instanceof DateTime) {
            if (false !== $converted = Date::mongo()->convertTo($provided)) {
                return $converted;
            }
            return null;
        }
        if ($provided instanceof UTCDateTime) {
            return $provided;
        }

        return null;
    }

    /**
     * Lazy convert BSONDocuments and BSONArray's to php arrays.
     *
     * @param array|BSONArray|BSONDocument $item
     *
     * @return array|mixed
     */
    public static function arrayToPhp(mixed $item): mixed
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
     * Convert a string, array, or ObjectId to an ObjectId.
     * Handles the formats of: array('oid' => '< 24 character ID >'), array('$oid' => '< 24 character ID >'), '< 24 character ID >'.
     *
     * @param array|string|ObjectId|null $id
     *
     * @return null|ObjectId
     */
    public static function toObjectId(ObjectId|array|string|null $id): ?ObjectId
    {
        if (null === $id) {
            return null;
        }
        if (is_string($id) && 24 === strlen($id)) {
            return new ObjectId($id);
        }
        if ($id instanceof ObjectId) {
            return $id;
        }

        if (is_array($id)) {
            if (array_key_exists('oid', $id) && is_string($id['oid']) && 24 === strlen($id['oid'])) {
                return self::toObjectId($id['oid']);
            }
            if (array_key_exists('$oid', $id) && is_string($id['$oid']) && 24 === strlen($id['$oid'])) {
                return self::toObjectId($id['$oid']);
            }
        }

        return null;
    }

    /**
     * @param string $id
     * @return ObjectIdå
     */
    public static function stringToObjectId(string $id): ObjectId
    {
        return self::toObjectId($id);
    }

    /**
     * Convert an ObjectId to a string.
     *
     * @param ObjectId $id
     *
     * @return string
     */
    public static function objectIdToString(ObjectId $id): string
    {
        return $id->__toString();
    }

    /**
     * Convert a single variable to a json friendly type.
     *
     * @param mixed $v
     *
     * @return mixed
     * @noinspection PhpExpressionAlwaysNullInspection
     */
    public static function typeToJsonFriendly(mixed $v): mixed
    {
        $val = $v;
        if (is_string($val)) {
            return $val;
        } else if (is_int($val)) {
            return $val;
        } else if (is_float($val)) {
            return $val;
        } else if (is_bool($val)) {
            return $val;
        } else if (is_null($val)) {
            return $val;
        } else if (is_object($val)) {
            $val = clone $v; // it's an object
            if (method_exists($val, 'jsonSerialize')) {
                if ($val instanceof ObjectId) {
                    return MondocTypes::objectIdToString($val);
                }
                if ($val instanceof UTCDateTime) {
                    return $val->jsonSerialize()['$date']['$numberLong'];
                }
                return self::typeToJsonFriendly(
                    $val->jsonSerialize()
                );
            }
        }

        if ($val instanceof BSONArray) {
            return self::typeToJsonFriendly(
                self::arrayToPhp($val)
            );
        }
        if ($val instanceof BSONDocument) {
            return self::typeToJsonFriendly(
                self::arrayToPhp($val)
            );
        }
        if ($val instanceof DateTime) {
            return $val->format('Uv');
        }
        if ($val instanceof stdClass) {
            return (array)$val;
        }

        if (is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::typeToJsonFriendly($value);
            }
            return $val;
        }

        return $val;
    }
}
