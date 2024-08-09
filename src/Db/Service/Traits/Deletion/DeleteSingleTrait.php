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

namespace District5\Mondoc\Db\Service\Traits\Deletion;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Helper\FilterFormatter;
use District5\Mondoc\Helper\MondocTypes;
use MongoDB\BSON\ObjectId;
use MongoDB\DeleteResult;

/**
 * Trait DeleteSingleTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Deletion
 */
trait DeleteSingleTrait
{
    /**
     * Delete a single document from the collection by a given ID.
     *
     * @param string|ObjectId $id
     *
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function delete(ObjectId|string $id): bool
    {
        return self::deleteOne(
            [
                '_id' => MondocTypes::toObjectId($id)
            ]
        );
    }

    /**
     * Delete a single document from the collection by a given filter.
     *
     * @param array $filter
     * @param array $options
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function deleteOne(array $filter, array $options = []): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $delete = $collection->deleteOne(
            FilterFormatter::format(
                $filter
            ),
            $options
        );

        return $delete instanceof DeleteResult ? $delete->getDeletedCount() : false;
    }
}
