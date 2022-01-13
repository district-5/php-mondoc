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
