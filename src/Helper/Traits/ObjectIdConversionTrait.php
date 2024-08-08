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

namespace District5\Mondoc\Helper\Traits;

use District5\Mondoc\Helper\MondocTypes;
use MongoDB\BSON\ObjectId;

/**
 * Trait ObjectIdConversionTrait
 *
 * @package District5\Mondoc\Helper\Traits
 */
trait ObjectIdConversionTrait
{
    /**
     * Get a new ObjectId.
     *
     * @return ObjectId
     */
    public static function newObjectId(): ObjectId
    {
        return new ObjectId();
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
     * Iterate through an array of ObjectIds to deduplicate them.
     *
     * @param ObjectId[] $ids
     *
     * @return ObjectId[]
     */
    public static function deduplicateArrayOfObjectIds(array $ids): array
    {
        $tmp = [];
        $final = [];
        foreach ($ids as $id) {
            $newId = self::toObjectId($id);
            if (null !== $newId && !in_array($stringId = MondocTypes::objectIdToString($newId), $tmp)) {
                $final[] = $newId;
                $tmp[] = $stringId;
            }
        }

        return $final;
    }
}
