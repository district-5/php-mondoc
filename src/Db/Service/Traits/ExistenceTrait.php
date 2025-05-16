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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\FilterFormatter;
use District5\Mondoc\Helper\MondocTypes;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\BSON\ObjectId;

/**
 * Trait ExistenceTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait ExistenceTrait
{
    /**
     * Does a document exist? This is likely not a method you need to use, although if you're simply checking
     * for existence, you can call it.
     *
     * @param QueryBuilder $builder
     * @return bool
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function existsWithQueryBuilder(QueryBuilder $builder): bool
    {
        return self::exists(
            FilterFormatter::format(
                $builder->getArrayCopy()
            ),
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Does a document exist? This is likely not a method you need to use, although if you're simply checking
     * for existence, you can call it.
     *
     * @param array $criteria
     * @param array $options
     * @return bool
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function exists(array $criteria, array $options = []): bool
    {
        $criteria = FilterFormatter::format($criteria);
        if (!array_key_exists('projection', $options)) {
            $options['projection'] = ['_id' => 1];
        }
        if (array_key_exists('sort', $options)) {
            $opts = array_merge($options, ['limit' => 1]);
            $results = self::getMultiByCriteria($criteria, $opts);
            return count($results) === 1;
        }

        if (array_key_exists('limit', $options)) {
            unset($options['limit']);
        }
        $collection = self::getCollection(
            get_called_class()
        );
        $match = $collection->findOne($criteria, $options);
        if ($match) {
            return true;
        }
        return false;
    }

    /**
     * Does a document with a given ID exist?
     *
     * @param ObjectId|string $documentId
     * @return bool
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function existsById(ObjectId|string $documentId): bool
    {
        return self::exists(
            [
                '_id' => [
                    '$eq' => MondocTypes::toObjectId($documentId),
                ],
            ]
        );
    }
}
