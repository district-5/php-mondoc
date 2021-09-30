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

use District5\Mondoc\Helper\PaginatedQueryHelper;
use District5\Mondoc\Model\MondocAbstractModel;

/**
 * Trait PaginationTrait.
 *
 * @package District5\Mondoc\Traits
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
     * @return PaginatedQueryHelper
     * @noinspection PhpUnused
     */
    public static function getPaginationQueryHelper(int $currentPageNumber, int $perPage, array $filter = []): PaginatedQueryHelper
    {
        return new PaginatedQueryHelper(
            self::countAll($filter),
            $currentPageNumber,
            $perPage
        );
    }

    /**
     * Get a page of results for a specific query filter.
     *
     * @param PaginatedQueryHelper $paginator
     * @param array $filter
     * @param string|null $sortByField
     * @param int $sortDirection
     * @return MondocAbstractModel[]
     * @noinspection PhpUnused
     */
    public static function getPage(PaginatedQueryHelper $paginator, array $filter = [], ?string $sortByField = null, int $sortDirection = -1): array
    {
        $options = [
            'skip' => $paginator->getSkip(),
            'limit' => $paginator->getLimit()
        ];
        if ($sortByField !== null) {
            $options['sort'] = [$sortByField => $sortDirection];
        }
        return self::getMultiByCriteria(
            $filter,
            $options
        );
    }
}
