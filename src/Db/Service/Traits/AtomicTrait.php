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

namespace District5\Mondoc\Db\Service\Traits;

use District5\Mondoc\Db\Service\Traits\Atomic\DecrementTrait;
use District5\Mondoc\Db\Service\Traits\Atomic\IncrementTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use MongoDB\BSON\ObjectId;

/**
 * Trait AtomicTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait AtomicTrait
{
    use IncrementTrait;
    use DecrementTrait;

    /**
     * Perform an atomic operation.
     *
     * @param ObjectId $id
     * @param array $update
     *
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    protected static function atomic(ObjectId $id, array $update): bool
    {
        return self::updateOne(
            ['_id' => $id], // Formatted in the updateOne method
            $update // Formatted in the updateOne method
        );
    }
}
