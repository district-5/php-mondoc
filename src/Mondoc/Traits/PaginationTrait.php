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
