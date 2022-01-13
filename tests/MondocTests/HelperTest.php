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

namespace District5Tests\MondocTests;

use DateTime;
use District5\Mondoc\Helper\MondocMongoTypeConverter;
use District5\Mondoc\Helper\PaginatedQueryHelper;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * @internal
 */
class HelperTest extends MondocBaseTest
{
    public function testBuiltInFunctionsAreCorrectBeforeRunningTests()
    {
        $this->assertEquals('1609927725871', rtrim($this->getMongoDate()->toDateTime()->format('Uu'), '0'));
        $this->assertEquals('1609927725871000', $this->getPhpDate()->format('Uu'));
        $this->assertEquals('1609927725871', $this->getPhpDate()->format('Uv'));
    }

    public function testMongoDateToPhpDateTime()
    {
        $this->assertEquals('1609927725871', rtrim(MondocMongoTypeConverter::dateToPHPDateTime($this->getMongoDate())->format('Uu'), '0'));
        $this->assertEquals('1609927725871', rtrim(MondocMongoTypeConverter::dateToPHPDateTime($this->getPhpDate())->format('Uu'), '0'));
    }

    public function testPhpDateTimeToMongoDate()
    {
        $this->assertEquals('1609927725871', rtrim(MondocMongoTypeConverter::phpDateToMongoDateTime($this->getMongoDate())->toDateTime()->format('Uu'), '0'));
        $this->assertEquals('1609927725871', rtrim(MondocMongoTypeConverter::phpDateToMongoDateTime($this->getPhpDate())->toDateTime()->format('Uu'), '0'));
    }

    public function testArrayIsArray()
    {
        $d = ['foo' => 'bar'];
        $this->assertIsArray(MondocMongoTypeConverter::arrayToPhp($d));
        $this->assertArrayHasKey('foo', MondocMongoTypeConverter::arrayToPhp($d));
    }

    public function testBsonDocumentIsArray()
    {
        $d = new BSONDocument(['foo' => 'bar']);
        $this->assertIsArray(MondocMongoTypeConverter::arrayToPhp($d));
        $this->assertArrayHasKey('foo', MondocMongoTypeConverter::arrayToPhp($d));
    }

    public function testBsonArrayIsArray()
    {
        $d = new BSONArray(['foo' => 'bar']);
        $this->assertIsArray(MondocMongoTypeConverter::arrayToPhp($d));
        $this->assertArrayHasKey('foo', MondocMongoTypeConverter::arrayToPhp($d));
    }

    public function testPagination()
    {
        $paginator = PaginatedQueryHelper::init(101, 2, 10);
        $this->assertEquals(101, $paginator->getTotalResults());
        $this->assertEquals(11, $paginator->getTotalPages());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertFalse($paginator->isFirstPage());
        $this->assertFalse($paginator->isLastPage());
    }

    public function testPaginationFirstPage()
    {
        $paginator = PaginatedQueryHelper::init(100, 1, 10);
        $this->assertEquals(10, $paginator->getTotalPages());
        $this->assertFalse($paginator->hasPreviousPage());
        $this->assertTrue($paginator->hasNextPage());
        $this->assertTrue($paginator->isFirstPage());
        $this->assertFalse($paginator->isLastPage());
    }

    public function testPaginationLastPage()
    {
        $paginator = PaginatedQueryHelper::init(100, 10, 10);
        $this->assertEquals(10, $paginator->getTotalPages());
        $this->assertTrue($paginator->hasPreviousPage());
        $this->assertFalse($paginator->hasNextPage());
        $this->assertFalse($paginator->isFirstPage());
        $this->assertTrue($paginator->isLastPage());
    }

    /**
     * @return DateTime
     */
    private function getPhpDate()
    {
        return new DateTime('2021-01-06 10:08:45.871000');
    }

    /**
     * @return UTCDateTime
     */
    private function getMongoDate()
    {
        return new UTCDateTime(1609927725871);
    }
}
