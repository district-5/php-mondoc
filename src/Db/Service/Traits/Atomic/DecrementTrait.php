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

namespace District5\Mondoc\Db\Service\Traits\Atomic;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use MongoDB\BSON\ObjectId;

/**
 * Trait DecrementTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Atomic
 */
trait DecrementTrait
{
    /**
     * Decrement a field by a given delta, using a whole number as the delta. IE passing `1` would DECREASE a
     * number by 1.
     *
     * @param ObjectId $id
     * @param string $field
     * @param int $delta
     *
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function dec(ObjectId $id, string $field, int $delta = 1): bool
    {
        return self::atomic(
            $id,
            ['$inc' => [$field => ($delta - ($delta * 2))]]
        );
    }

    /**
     * Decrement multiple fields by a given delta, using a whole number as the delta. IE passing `1` would DECREASE a
     * number by 1.
     *
     * @param ObjectId $id
     * @param array $fieldsToDeltas
     * @return bool
     * @throws MondocConfigConfigurationException
     * @example
     *      ->decMulti(
     *          $model->getObjectId(),
     *          ['age' => 1, 'logins' => 1]
     *      )
     *
     */
    public static function decMulti(ObjectId $id, array $fieldsToDeltas): bool
    {
        foreach ($fieldsToDeltas as $field => $delta) {
            $fieldsToDeltas[$field] = ($delta - ($delta * 2));
        }
        return self::atomic(
            $id,
            ['$inc' => $fieldsToDeltas]
        );
    }
}
