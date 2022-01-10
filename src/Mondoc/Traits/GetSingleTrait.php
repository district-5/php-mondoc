<?php

/**
 * District5 - Mondoc
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
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
        if (in_array('sort', $options)) {
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
            $clz = $calledClass::$modelClassName;
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
