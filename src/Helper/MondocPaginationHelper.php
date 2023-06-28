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

namespace District5\Mondoc\Helper;

/**
 * Class PaginateHelper.
 *
 * @package District5\Mondoc\Helper
 */
class MondocPaginationHelper
{
    /**
     * @var int
     */
    protected int $currentPage = 1;

    /**
     * @var int
     */
    protected int $perPage = 10;

    /**
     * @var int
     */
    protected int $totalPages = 1;

    /**
     * @var int
     */
    protected int $totalResults = 0;

    /**
     * @var int
     */
    protected int $skip = 0;

    /**
     * MondocPaginationHelper constructor.
     *
     * @param int $totalResults
     * @param int $currentPage
     * @param int $perPage (optional) default 10
     */
    public function __construct(int $totalResults, int $currentPage, int $perPage = 10)
    {
        $this->cleanPerPage($perPage);
        $this->cleanCurrentPage($currentPage);
        $this->cleanTotalResults($totalResults);
        $this->establishTotalPages();
        $this->establishSkip();
    }

    /**
     * Clean the per page.
     *
     * @param int $perPage
     */
    private function cleanPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        if (!is_numeric($this->perPage) || $this->perPage < 1) {
            $this->perPage = 10;
        }
        $this->perPage = intval($this->perPage);
    }

    /**
     * Clean the current page.
     *
     * @param int $currentPage
     */
    private function cleanCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
        if (!is_numeric($this->currentPage) || $this->currentPage < 1) {
            $this->currentPage = 1;
        }
        $this->currentPage = intval($this->currentPage);
    }

    /**
     * Clean the total results.
     *
     * @param int $totalResults
     */
    private function cleanTotalResults(int $totalResults): void
    {
        $this->totalResults = $totalResults;
        if (!is_numeric($this->totalResults) || $this->totalResults < 1) {
            $this->totalResults = 0;
        }
        $this->totalResults = intval($this->totalResults);
    }

    /**
     * Establish the total number of pages.
     */
    private function establishTotalPages(): void
    {
        $this->totalPages = intval(ceil($this->totalResults / $this->perPage));
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
    }

    /**
     * Establish the number of results to skip, or the offset.
     */
    private function establishSkip(): void
    {
        $this->skip = (($this->currentPage * $this->perPage) - $this->perPage);
        if ($this->skip < 0) {
            $this->skip = 0;
        }
    }

    /**
     * @param int $totalResults
     * @param int $currentPage
     * @param int $perPage (optional) default 10
     *
     * @return MondocPaginationHelper
     * @noinspection PhpUnused
     */
    public static function init(int $totalResults, int $currentPage, int $perPage = 10): MondocPaginationHelper
    {
        return new MondocPaginationHelper($totalResults, $currentPage, $perPage);
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isFirstPage(): bool
    {
        return 1 === $this->getCurrentPage();
    }

    /**
     * Get the current page.
     *
     * @return int
     * @noinspection PhpUnused
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isLastPage(): bool
    {
        return $this->getCurrentPage() === $this->getTotalPages();
    }

    /**
     * Get the total number of pages.
     *
     * @return int
     * @noinspection PhpUnused
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasMultiplePages(): bool
    {
        return $this->getTotalPages() > 1;
    }

    /**
     * Get the total number of results in the set.
     *
     * @return int
     */
    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    /**
     * Get the query limit number (per page).
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->perPage;
    }

    /**
     * Get the query skip number.
     *
     * @return int
     */
    public function getSkip(): int
    {
        return $this->skip;
    }

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->getCurrentPage() < $this->getTotalPages();
    }

    /**
     * Should a specific page number be shown on the UI?
     *
     * @param int $proposed
     * @param ?int $howManyPagesEachSide
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function shouldShowPageOnUi(int $proposed, ?int $howManyPagesEachSide): bool
    {
        if (1 === $this->getTotalPages()) {
            return false;
        }
        if (null === $howManyPagesEachSide || $howManyPagesEachSide < 1) {
            return true;
        }
        $paginationStartCounter = $this->getCurrentPage() - $howManyPagesEachSide;
        $paginationEndCounter = $this->getCurrentPage() + $howManyPagesEachSide;

        if ($paginationStartCounter < 0) {
            $paginationEndCounter += abs($paginationStartCounter) + 1;
            $paginationStartCounter = 1;
        }
        if ($paginationEndCounter > $this->getTotalPages()) {
            $diffBetweenCountAndEnd = $this->getTotalPages() - $paginationEndCounter;
            $paginationStartCounter += $diffBetweenCountAndEnd;
        }
        if (0 === $paginationStartCounter) {
            $paginationStartCounter = 1;
            ++$paginationEndCounter;
        }

        return $proposed >= $paginationStartCounter && $proposed <= $paginationEndCounter;
    }
}
