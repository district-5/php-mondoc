<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

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

namespace District5Tests\MondocTests\Helper;

use District5\Mondoc\Helper\MondocPaginationHelper;
use District5Tests\MondocTests\MondocBaseTestAbstract;

/**
 * Class PaginationTest.
 *
 * @package District5Tests\MondocTests\Helper
 */
class PaginationTest extends MondocBaseTestAbstract
{
    public function testBasicFirstPagePagination()
    {
        $paginator = MondocPaginationHelper::init(100, 1, 10);

        $this->assertEquals(100, $paginator->getTotalResults());
        $this->assertEquals(1, $paginator->getCurrentPage());
        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(0, $paginator->getSkip());
        $this->assertTrue($paginator->isFirstPage());
        $this->assertFalse($paginator->isLastPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertFalse($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasMultiplePages());

        $arrayValue = $paginator->asArray();
        $this->assertIsArray($arrayValue);
        $this->assertArrayHasKey('results', $arrayValue);
        $this->assertArrayHasKey('page', $arrayValue);
        $this->assertArrayHasKey('pages', $arrayValue);
        $this->assertArrayHasKey('perPage', $arrayValue);

        $this->assertIsInt($arrayValue['results']);
        $this->assertIsInt($arrayValue['page']);
        $this->assertIsInt($arrayValue['pages']);
        $this->assertIsInt($arrayValue['perPage']);

        $this->assertEquals(100, $arrayValue['results']);
        $this->assertEquals(1, $arrayValue['page']);
        $this->assertEquals(10, $arrayValue['perPage']);
        $this->assertEquals(10, $arrayValue['pages']);
    }

    public function testInvalidPageDetails()
    {
        $paginator = MondocPaginationHelper::init(100, -1, -10);

        $this->assertEquals(100, $paginator->getTotalResults());
        $this->assertEquals(1, $paginator->getCurrentPage());
        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(0, $paginator->getSkip());
        $this->assertTrue($paginator->isFirstPage());
        $this->assertFalse($paginator->isLastPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertFalse($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasMultiplePages());
    }

    public function testBasicNonFirstPagePagination()
    {
        $paginator = MondocPaginationHelper::init(100, 2, 10);

        $this->assertEquals(100, $paginator->getTotalResults());
        $this->assertEquals(2, $paginator->getCurrentPage());
        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(10, $paginator->getSkip());
        $this->assertFalse($paginator->isFirstPage());
        $this->assertFalse($paginator->isLastPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasMultiplePages());
    }

    public function testBasicLastPagePagination()
    {
        $paginator = MondocPaginationHelper::init(100, 10, 10);

        $this->assertEquals(100, $paginator->getTotalResults());
        $this->assertEquals(10, $paginator->getCurrentPage());
        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(90, $paginator->getSkip());
        $this->assertFalse($paginator->isFirstPage());
        $this->assertTrue($paginator->isLastPage());
        $this->assertFalse($paginator->hasNextPage());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasMultiplePages());
    }

    public function testBasicInvalidPaginationDataForPagination()
    {
        $paginator = MondocPaginationHelper::init(100, 15, 10); // only 10 pages

        $this->assertEquals(100, $paginator->getTotalResults());
        $this->assertEquals(10, $paginator->getCurrentPage());
        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(90, $paginator->getSkip());
        $this->assertFalse($paginator->isFirstPage());
        $this->assertTrue($paginator->isLastPage());
        $this->assertFalse($paginator->hasNextPage());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasMultiplePages());

        $paginatorAlt = MondocPaginationHelper::init(-1, 1, 10); // cannot have negative results
        $this->assertEquals(0, $paginatorAlt->getTotalResults());
    }

    public function testUiPageDisplayControls()
    {
        $paginatorOneResult = MondocPaginationHelper::init(1, 1, 10); // only 10 pages
        $this->assertFalse($paginatorOneResult->shouldShowPageOnUi(1, 1));

        $paginatorUnlimited = MondocPaginationHelper::init(100, 1, 10); // only 10 pages
        $this->assertTrue($paginatorUnlimited->shouldShowPageOnUi(1, null)); // null allows all

        $paginator = MondocPaginationHelper::init(100, 2, 10); // only 10 pages

        $this->assertTrue($paginator->shouldShowPageOnUi(2, 1));
        $this->assertTrue($paginator->shouldShowPageOnUi(2, 2));
        $this->assertFalse($paginator->shouldShowPageOnUi(5, 1));

        $this->assertTrue($paginator->shouldShowPageOnUi(5, 10));
    }
}
