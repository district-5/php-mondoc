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

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\FilterFormatter;
use District5\Mondoc\Helper\MondocTypes;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\Model\BSONDocument;

/**
 * Trait GetMultiTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait GetMultiTrait
{
    /**
     * Get multiple models by passing in an instance of QueryBuilder.
     *
     * @param QueryBuilder $builder
     *
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public static function getMultiByQueryBuilder(QueryBuilder $builder): array
    {
        return self::getMultiByCriteria(
            $builder->getArrayCopy(), // Formatted in the getMultiByCriteria method
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Get multiple models by a given filter, using given options.
     *
     * @param array $filter
     * @param array $options
     *
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public static function getMultiByCriteria(array $filter = [], array $options = []): array
    {
        $calledClass = get_called_class();
        $collection = self::getCollection(
            $calledClass
        );
        $cursor = $collection->find(
            FilterFormatter::format($filter),
            $options
        );
        $objs = [];
        /* @var $calledClass MondocAbstractService */
        $clz = $calledClass::getModelClass();
        /* @var $clz MondocAbstractModel - it's not. It's actually a string. */
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($cursor as $k => $v) {
            /* @var $v BSONDocument */
            $m = $clz::inflateSingleBsonDocument($v);
            $m->setMongoCollection($collection);
            $objs[] = $m;
        }

        return $objs;
    }

    /**
     * Get multiple models by the ObjectIds.
     *
     * @param array $ids
     *
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public static function getByIds(array $ids): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $cleansed = [];
        foreach ($ids as $id) {
            if (null !== $clean = MondocTypes::toObjectId($id)) {
                $cleansed[] = $clean;
            }
        }
        if (0 === count($cleansed)) {
            return [];
        }

        return self::getMultiByCriteria([
            '_id' => [
                '$in' => $cleansed
            ]
        ]);
    }

    /**
     * Get multiple documents, where $key equals $value. Utilises the '$eq' operator.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public static function getMultiWhereKeyEqualsValue(string $key, mixed $value): array
    {
        return self::getMultiByCriteria([
            $key => ['$eq' => $value] // Formatted in the getMultiByCriteria method
        ]);
    }

    /**
     * Get multiple documents, where $key does not equal $value. Utilises the '$ne' operator.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public static function getMultiWhereKeyDoesNotEqualValue(string $key, mixed $value): array
    {
        return self::getMultiByCriteria([
            $key => ['$ne' => $value]
        ]);
    }
}
