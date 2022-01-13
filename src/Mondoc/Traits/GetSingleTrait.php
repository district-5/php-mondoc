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

namespace District5\Mondoc\Traits;

use District5\Mondoc\Helper\MondocMongoTypeConverter;
use District5\Mondoc\Model\MondocAbstractModel;
use District5\Mondoc\Service\MondocAbstractService;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\BSON\ObjectId;
use MongoDB\Model\BSONDocument;

/**
 * Trait GetSingleTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait GetSingleTrait
{
    /**
     * Get a single model by passing in an instance of QueryBuilder.
     *
     * @param QueryBuilder $builder
     *
     * @return MondocAbstractModel
     */
    public static function getOneByQueryBuilder(QueryBuilder $builder): ?MondocAbstractModel
    {
        return self::getOneByCriteria(
            $builder->getArrayCopy(),
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Get a single model by a given filter, using given options.
     *
     * @param array $filter
     * @param array $options
     *
     * @return MondocAbstractModel
     */
    public static function getOneByCriteria(array $filter = [], array $options = []): ?MondocAbstractModel
    {
        if (array_key_exists('sort', $options)) {
            $opts = array_merge($options, ['limit' => 1]);
            $results = self::getMultiByCriteria($filter, $opts);
            if (count($results) === 1) {
                return $results[0];
            }
            return null;
        }
        $calledClass = get_called_class();
        $collection = self::getCollection(
            $calledClass
        );
        $match = $collection->findOne($filter, $options);
        if ($match) {
            /* @var $match BSONDocument */
            /* @var $calledClass MondocAbstractService */
            $clz = $calledClass::getModelClass();
            /* @var $clz MondocAbstractModel - it's not. It's actually a string. */
            $m = $clz::inflateSingleBsonDocument($match);
            $m->setMongoCollection($collection);

            return $m;
        }

        return null;
    }

    /**
     * Get a single model by an ID.
     *
     * @param ObjectId|string $id
     *
     * @return null|MondocAbstractModel
     * @noinspection PhpUnused
     */
    public static function getById($id): ?MondocAbstractModel
    {
        if (null === $converted = MondocMongoTypeConverter::convertToMongoId($id)) {
            return null;
        }
        $service = get_called_class();
        /* @var $service MondocAbstractService - it's not. It's actually a string. */
        return $service::getOneByCriteria(
            ['_id' => $converted]
        );
    }
}
