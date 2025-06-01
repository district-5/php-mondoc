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

namespace District5\Mondoc\Db\Service;

use District5\Mondoc\Db\Service\ServiceSub\AggregateSubService;
use District5\Mondoc\Db\Service\Traits\AtomicTrait;
use District5\Mondoc\Db\Service\Traits\CountableTrait;
use District5\Mondoc\Db\Service\Traits\DeletionTrait;
use District5\Mondoc\Db\Service\Traits\DistinctValuesTrait;
use District5\Mondoc\Db\Service\Traits\ExistenceTrait;
use District5\Mondoc\Db\Service\Traits\FieldOperationsTrait;
use District5\Mondoc\Db\Service\Traits\GetMultiTrait;
use District5\Mondoc\Db\Service\Traits\GetSingleTrait;
use District5\Mondoc\Db\Service\Traits\OperatorsTrait;
use District5\Mondoc\Db\Service\Traits\PaginationTrait;
use District5\Mondoc\Db\Service\Traits\PersistenceTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\MondocConfig;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\Collection;
use MongoDB\Database;

/**
 * Abstract class MondocAbstractService.
 *
 * @package District5\Mondoc\Db\Service
 */
abstract class MondocAbstractService
{
    use AtomicTrait;
    use CountableTrait;
    use DeletionTrait;
    use DistinctValuesTrait;
    use GetMultiTrait;
    use GetSingleTrait;
    use ExistenceTrait;
    use FieldOperationsTrait;
    use OperatorsTrait;
    use PaginationTrait;
    use PersistenceTrait;

    /**
     * Retrieve the Database instance.
     *
     * @return null|Database
     * @throws MondocConfigConfigurationException
     */
    public static function getMongo(): ?Database
    {
        return MondocConfig::getInstance()->getDatabase(
            self::getConnectionId()
        );
    }

    /**
     * Get the connection ID to use from the MondocConfig manager. Defaults to 'default'.
     *
     * @return string
     */
    protected static function getConnectionId(): string
    {
        return 'default';
    }

    /**
     * Retrieve the Collection instance.
     *
     * @param string|null $clz
     *
     * @return Collection
     * @throws MondocConfigConfigurationException
     */
    public static function getCollection(string|null $clz = null): Collection
    {
        if (null === $clz) {
            $clz = get_called_class();
        }
        /* @var $clz MondocAbstractService - it's not. It's actually a string. */
        return MondocConfig::getInstance()->getCollection(
            $clz::getCollectionName(),
            $clz::getConnectionId()
        );
    }

    /**
     * Get the collection name.
     *
     * @return string
     */
    abstract protected static function getCollectionName(): string;

    /**
     * @return AggregateSubService
     */
    public static function aggregate(): AggregateSubService
    {
        return new AggregateSubService(get_called_class());
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
     * @return string
     * @throws MondocServiceMapErrorException
     */
    public static function getMondocModelClass(): string
    {
        return MondocConfig::getInstance()->getModelForService(
            get_called_class()
        );
    }
}
