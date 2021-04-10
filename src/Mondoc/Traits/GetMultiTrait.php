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
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

/**
 * Trait GetMultiTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait GetMultiTrait
{
    /**
     * Get multiple models by a given filter, using given options.
     *
     * @param array $filter
     * @param array $options
     *
     * @return MondocAbstractModel[]
     * @noinspection PhpUnused
     */
    public static function getMultiByCriteria(array $filter = [], array $options = []): array
    {
        $calledClass = get_called_class();
        /* @var $calledClass MondocAbstractService */
        $collection = self::getCollection(
            $calledClass
        );
        /* @var $collection Collection */
        $cursor = $collection->find($filter, $options);
        if ($cursor) {
            $objs = [];
            $clz = $calledClass::$modelClassName;
            /* @var $clz MondocAbstractModel - it's not. It's actually a string. */
            foreach ($cursor as $k => $v) {
                /* @var $v BSONDocument */
                $m = $clz::inflateSingleBsonDocument($v);
                $m->setMongoCollection($collection);
                $objs[] = $m;
            }

            return $objs;
        }

        return [];
    }

    /**
     * Get multiple models by passing in an instance of QueryBuilder.
     *
     * @param QueryBuilder $builder
     *
     * @return MondocAbstractModel[]
     */
    public static function getMultiByQueryBuilder(QueryBuilder $builder): array
    {
        return self::getMultiByCriteria(
            $builder->getArrayCopy(),
            $builder->getOptions()->getArrayCopy()
        );
    }

    /**
     * Get multiple models by the ObjectIds.
     *
     * @param array $ids
     *
     * @return MondocAbstractModel[]
     */
    public static function getByIds(array $ids): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $cleansed = [];
        foreach ($ids as $id) {
            if (null !== $clean = MondocMongoTypeConverter::convertToMongoId($id)) {
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
}
