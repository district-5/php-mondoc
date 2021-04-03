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
