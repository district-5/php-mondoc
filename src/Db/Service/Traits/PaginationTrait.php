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
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\MondocPaginationHelper;
use District5\Mondoc\Helper\MondocTypes;
use District5\MondocBuilder\QueryBuilder;
use InvalidArgumentException;
use MongoDB\BSON\ObjectId;

/**
 * Trait PaginationTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait PaginationTrait
{
    /**
     * Get a paginator for a specific query filter.
     *
     * @param int $currentPageNumber
     * @param int $perPage
     * @param array $filter
     *
     * @return MondocPaginationHelper
     *
     * @throws MondocConfigConfigurationException
     * @see PaginationTrait::getPaginationHelperForObjectIdPagination
     */
    public static function getPaginationHelper(int $currentPageNumber, int $perPage, array $filter = []): MondocPaginationHelper
    {
        return new MondocPaginationHelper(
            self::countAll($filter), // Formatted in the countAll method
            $currentPageNumber,
            $perPage,
            $filter
        );
    }

    /**
     * Get a paginator for a specific query filter. This method is to be used for _id based pagination.
     *
     * @param int $perPage
     * @param array $filter
     *
     * @return MondocPaginationHelper
     *
     * @throws MondocConfigConfigurationException
     * @see PaginationTrait::getPaginationHelper
     */
    public static function getPaginationHelperForObjectIdPagination(int $perPage, array $filter = []): MondocPaginationHelper
    {
        return self::getPaginationHelper(
            1,
            $perPage,
            $filter
        );
    }

    /**
     * Get paginator by a query builder
     *
     * @param QueryBuilder $builder
     * @param int $currentPageNumber
     * @param int $perPage
     *
     * @return MondocPaginationHelper
     *
     * @throws MondocConfigConfigurationException
     * @see PaginationTrait::getPaginationHelperForObjectIdPagination
     */
    public static function getPaginationHelperByQueryBuilder(QueryBuilder $builder, int $currentPageNumber, int $perPage): MondocPaginationHelper
    {
        $query = $builder->getArrayCopy();
        return new MondocPaginationHelper(
            self::countAll($query),
            $currentPageNumber,
            $perPage,
            $query
        );
    }

    /**
     * Get a page of results for a specific query filter.
     *
     * @param MondocPaginationHelper $paginator
     * @param string|null $sortByField (optional) default '_id'
     * @param int $sortDirection (optional) default -1
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function getPage(MondocPaginationHelper $paginator, ?string $sortByField = '_id', int $sortDirection = -1): array
    {
        $options = [
            'skip' => $paginator->getSkip(),
            'limit' => $paginator->getLimit()
        ];
        if ($sortByField !== null) {
            $options['sort'] = [$sortByField => $sortDirection];
        }
        return self::getMultiByCriteria(
            $paginator->getFilter(), // Formatted in the getMultiByCriteria method
            $options
        );
    }

    /**
     * Get a page of results for a specific query filter, passing in options.
     *
     * @param MondocPaginationHelper $paginator
     * @param array $options
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function getPageWithOptions(MondocPaginationHelper $paginator, array $options): array
    {
        if (!array_key_exists('skip', $options)) {
            $options['skip'] = $paginator->getSkip();
        }
        if (!array_key_exists('limit', $options)) {
            $options['limit'] = $paginator->getLimit();
        }

        return self::getMultiByCriteria(
            $paginator->getFilter(), // Formatted in the getMultiByCriteria method
            $options
        );
    }

    /**
     * Get a page of results for a specific query filter. This method is to be used for _id based pagination.
     *
     * @param MondocPaginationHelper $paginator
     * @param string|ObjectId|null $currentId
     * @param int $sortDirection
     * @return MondocAbstractModel[]
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public static function getPageByByObjectIdPagination(MondocPaginationHelper $paginator, ObjectId|string|null $currentId, int $sortDirection = -1): array
    {
        $filter = $paginator->getFilter();
        $options = [
            'limit' => $paginator->getLimit(),
            'sort' => ['_id' => $sortDirection]
        ];
        if ($currentId !== null) {
            if ($sortDirection === -1) {
                $filter['_id'] = ['$lt' => MondocTypes::toObjectId($currentId)];
            } else if ($sortDirection === 1) {
                $filter['_id'] = ['$gt' => MondocTypes::toObjectId($currentId)];
            } else {
                throw new InvalidArgumentException(
                    'Invalid sort direction. Expected int(1) or int(-1)'
                );
            }
        }

        return self::getMultiByCriteria(
            $filter, // Formatted in the getMultiByCriteria method
            $options
        );
    }
}
