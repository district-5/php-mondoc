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

namespace District5\Mondoc\Extensions\Retention;

use District5\Date\Date;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\MondocPaginationHelper;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\BSON\ObjectId;

/**
 * Class MondocRetentionService
 * @package District5\Mondoc\Extensions\Retention
 *
 * @method static MondocRetentionModel[] getMultiByCriteria(array $filter = [], array $options = [])
 * @method static MondocRetentionModel[] getMultiByQueryBuilder(QueryBuilder $builder)
 * @method static MondocRetentionModel[] getByIds(array $ids)
 * @method static MondocRetentionModel[] getMultiWhereKeyEqualsValue(string $key, $value)
 * @method static MondocRetentionModel[] getMultiWhereKeyDoesNotEqualValue(string $key, $value)
 * @method static MondocRetentionModel|null getOneByCriteria(array $filter = [], array $options = [])
 * @method static MondocRetentionModel|null getById(ObjectId|string $id)
 * @method static MondocRetentionModel|null getOneByQueryBuilder(QueryBuilder $builder)
 * @method static MondocRetentionModel|null getOneWhereKeyEqualsValue(string $key, mixed $value)
 * @method static MondocRetentionModel|null getOneWhereKeyDoesNotEqualValue(string $key, mixed $value)
 * @method static MondocRetentionModel[] getPage(MondocPaginationHelper $paginator, ?string $sortByField = '_id', int $sortDirection = -1): array
 * @method static MondocRetentionModel[] getPageByByObjectIdPagination(MondocPaginationHelper $paginator, ObjectId|string|null $currentId, int $sortDirection = -1)
 */
class MondocRetentionService extends MondocAbstractService
{
    /**
     * Create and save a retention model for a given model.
     * The model you're passing in must have a valid object ID (i.e. it must have been saved to the database).
     *
     * @param MondocAbstractModel $model
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function create(MondocAbstractModel $model): void
    {
        self::createStub($model)->save();
    }

    /**
     * Create a retention model for a given model, but don't save it.
     *
     * @param MondocAbstractModel $model
     * @return MondocRetentionModel
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public static function createStub(MondocAbstractModel $model): MondocRetentionModel
    {
        $m = new MondocRetentionModel();
        $m->setSourceModel($model);
        return $m;
    }

    /**
     * Get the latest retention model for a given model.
     *
     * @param string $class
     * @param ObjectId $objectId
     * @return MondocRetentionModel|null
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function getLatestRetentionModelForModel(string $class, ObjectId $objectId): ?MondocRetentionModel
    {
        return self::getOneByCriteria([
            'class' => ['$eq' => $class],
            'id' => ['$eq' => $objectId],
        ], [
            'sort' => ['cd' => -1],
        ]);
    }

    /**
     * Count the number of retention models for a given class.
     *
     * @param string $class
     * @return int
     * @throws MondocConfigConfigurationException
     */
    public static function countRetentionModelsForClassName(string $class): int
    {
        return self::countAll([
            'class' => ['$eq' => $class],
        ]);
    }

    /**
     * Count the number of retention models for a given object.
     * The model you're passing in must have a valid object ID (i.e. it must have been saved to the database).
     *
     * @param MondocAbstractModel $object
     * @return int
     * @throws MondocConfigConfigurationException
     */
    public static function countRetentionModelsForModel(MondocAbstractModel $object): int
    {
        return self::countAll([
            'class' => ['$eq' => $object::class],
            'id' => ['$eq' => $object->getObjectId()],
        ]);
    }

    /**
     * @param string|MondocAbstractModel $class
     * @param int $perPage
     * @param int $currentPage
     * @return MondocPaginationHelper
     * @throws MondocConfigConfigurationException
     * @see MondocRetentionService::getRetentionHistoryPaginationHelperForModel()
     */
    public static function getRetentionHistoryPaginationHelperForClassName(string|MondocAbstractModel $class, int $perPage, int $currentPage): MondocPaginationHelper
    {
        if ($class instanceof MondocAbstractModel) {
            $class = $class::class;
        }
        return self::getPaginationHelper(
            $currentPage,
            $perPage,
            [
                'class' => ['$eq' => $class],
            ]
        );
    }

    /**
     * @param MondocAbstractModel $source
     * @param int $perPage
     * @param int $currentPage
     * @return MondocPaginationHelper
     * @throws MondocConfigConfigurationException
     * @see MondocRetentionService::getRetentionHistoryPaginationHelperForClassName()
     */
    public static function getRetentionHistoryPaginationHelperForModel(MondocAbstractModel $source, int $perPage, int $currentPage): MondocPaginationHelper
    {
        return self::getPaginationHelper(
            $currentPage,
            $perPage,
            [
                'class' => ['$eq' => $source::class],
                'id' => ['$eq' => $source->getObjectId()],
            ]
        );
    }

    /**
     * @return array
     */
    protected static function getIndexes(): array
    {
        return [
            ['cd' => 1],
            ['class' => 1],
            ['class' => 1, 'id' => 1],
            ['cd' => -1, 'class' => 1, 'id' => 1],
            ['cd' => -1, 'class' => 1],
        ];
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public static function addIndexes(): void
    {
        $collection = self::getCollection();
        foreach (self::getIndexes() as $index) {
            $collection->createIndex($index);
        }
    }

    /**
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function hasIndexes(): bool
    {
        $collection = self::getCollection();
        $indexes = $collection->listIndexes();
        $desired = self::getIndexes();
        $count = 0;
        foreach ($indexes as $index) {
            if (in_array($index['key'], $desired)) {
                $count++;
            }
        }
        return $count === count($desired);
    }

    /**
     * Get a pagination helper for all retention models that have expired for a given class name.
     *
     * @param string|MondocAbstractModel $className
     * @param int $currentPage
     * @param int $perPage
     * @return MondocPaginationHelper
     * @throws MondocConfigConfigurationException
     */
    public static function getPaginatorForExpiredRetentionForClassName(string|MondocAbstractModel $className, int $currentPage, int $perPage): MondocPaginationHelper
    {
        if ($className instanceof MondocAbstractModel) {
            $className = $className::class;
        }
        return self::getPaginationHelper(
            $currentPage,
            $perPage,
            [
                'class' => ['$eq' => $className],
                'expiry' => ['$ne' => null, '$lt' => Date::mongo()->convertTo(Date::nowUtc())],
            ]
        );
    }

    /**
     * Get a pagination helper for all retention models that have expired for a given class name.
     *
     * @param MondocAbstractModel $object
     * @param int $currentPage
     * @param int $perPage
     * @return MondocPaginationHelper
     * @throws MondocConfigConfigurationException
     */
    public static function getPaginatorForExpiredRetentionForObject(MondocAbstractModel $object, int $currentPage, int $perPage): MondocPaginationHelper
    {
        return self::getPaginationHelper(
            $currentPage,
            $perPage,
            [
                'class' => ['$eq' => $object::class],
                'id' => ['$eq' => $object->getObjectId()],
                'expiry' => ['$ne' => null, '$lt' => Date::mongo()->convertTo(Date::nowUtc())],
            ]
        );
    }

    /**
     * Get a page of retention models by passing in a paginator.
     *
     * @param MondocPaginationHelper $paginator
     * @param string $sortByField
     * @param int $sortDirection
     * @return MondocRetentionModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function getRetentionPage(MondocPaginationHelper $paginator, string $sortByField = '_id', int $sortDirection = -1): array
    {
        return self::getPage(
            $paginator,
            $sortByField,
            $sortDirection
        );
    }

    /**
     * @return string
     */
    protected static function getCollectionName(): string
    {
        return 'mondoc_retention';
    }

    /**
     * @return string
     */
    protected static function getConnectionId(): string
    {
        return 'mondoc_retention';
    }
}
