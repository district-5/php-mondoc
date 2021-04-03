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

namespace District5\Mondoc\Service;

use District5\Mondoc\MondocConnections;
use District5\Mondoc\Traits\Aggregation\SumFieldTrait;
use District5\Mondoc\Traits\AtomicTrait;
use District5\Mondoc\Traits\CountableTrait;
use District5\Mondoc\Traits\DeletionTrait;
use District5\Mondoc\Traits\DistinctValuesTrait;
use District5\Mondoc\Traits\GetMultiTrait;
use District5\Mondoc\Traits\GetSingleTrait;
use District5\Mondoc\Traits\KeyOperationsTrait;
use District5\Mondoc\Traits\PersistenceTrait;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\Collection;
use MongoDB\Database;

/**
 * Class MondocAbstractService.
 *
 * @package District5\Mondoc\Service
 * @noinspection PhpUnused
 */
abstract class MondocAbstractService
{
    use GetSingleTrait;
    use GetMultiTrait;
    use CountableTrait;
    use AtomicTrait;
    use PersistenceTrait;
    use DeletionTrait;
    use KeyOperationsTrait;
    use SumFieldTrait;
    use DistinctValuesTrait;

    /**
     * @var string
     */
    protected static $modelClassName = '';

    /**
     * Retrieve the Database instance.
     *
     * @return null|Database
     * @noinspection PhpUnused
     */
    public static function getMongo(): ?Database
    {
        return MondocConnections::getInstance()->getDatabase(
            self::getConnectionId()
        );
    }

    /**
     * Retrieve the Collection instance.
     *
     * @param string $clz
     *
     * @return Collection
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function getCollection($clz): Collection
    {
        // @var $clz MondocAbstractService - it's not. It's actually a string.
        return MondocConnections::getInstance()->getCollection(
            $clz::getCollectionName(),
            $clz::getConnectionId()
        );
    }

    /**
     * Get an instance of the query builder.
     *
     * @return QueryBuilder
     */
    public static function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder();
    }

    /**
     * Get the collection name.
     *
     * @return string
     */
    abstract protected static function getCollectionName(): string;

    /**
     * Get the connection ID to use from the MondocConnection manager. Defaults to 'default'.
     *
     * @return string
     */
    protected static function getConnectionId(): string
    {
        return 'default';
    }
}
