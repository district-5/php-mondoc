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
     * @param bool $asMongo
     *
     * @return null|DateTime|UTCDateTime
     * @noinspection PhpUnused
     */
    protected function convertDateObject($date, bool $asMongo = false)
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
}
