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

use BackedEnum;
use DateTime;
use District5\Mondoc\Db\Model\MondocAbstractSubModel;
use District5\Mondoc\Helper\Traits\ArrayConversionTrait;
use District5\Mondoc\Helper\Traits\DateObjectConversionTrait;
use District5\Mondoc\Helper\Traits\ObjectIdConversionTrait;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\Type;
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
    use ObjectIdConversionTrait;
    use DateObjectConversionTrait;
    use ArrayConversionTrait;

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
            if ($val instanceof Type) {
                if ($val instanceof BSONArray || $val instanceof BSONDocument) {
                    return self::typeToJsonFriendly($val->getArrayCopy());
                }
                if ($val instanceof UTCDateTime) {
                    return self::dateToPHPDateTime($val)->format('Uv');
                }
                if ($val instanceof ObjectId) {
                    return MondocTypes::objectIdToString($val);
                }
                if ($val instanceof Decimal128) {
                    return $val->jsonSerialize()['$numberDecimal'];
                }
                if ($val instanceof Timestamp) {
                    return $val->jsonSerialize()['$timestamp'];
                }

                if (method_exists($val, 'jsonSerialize')) {
                    return self::typeToJsonFriendly(
                        $val->jsonSerialize()
                    );
                }
            }

            if (is_subclass_of($val, BackedEnum::class)) {
                return $val->value;
            }

            $val = clone $v; // it's an object

            if ($val instanceof DateTime) {
                return $val->format('Uv');
            }
            if ($val instanceof stdClass) {
                return (array)$val;
            }

            if ($val instanceof MondocAbstractSubModel) {
                $val = $val->asArray();
            } else {
                $val = (array)$val;
            }
        }

        if (is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::typeToJsonFriendly($value);
            }
            return $val;
        }

        return null;
    }
}
