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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\Model\HookedModel;
use District5Tests\MondocTests\TestObjects\Service\HookedService;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class QueryScopesTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class QueryScopesTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testScopeFiltersCorrectly(): void
    {
        $alice = new HookedModel();
        $alice->setName('Alice')->setAge(30);

        $bob = new HookedModel();
        $bob->setName('Bob')->setAge(15);

        $charlie = new HookedModel();
        $charlie->setName('Charlie')->setAge(45);

        HookedService::insertMulti([$alice, $bob, $charlie]);

        $adultsFilter = HookedService::scope('adults');
        $this->assertArrayHasKey('age', $adultsFilter);
        $this->assertEquals(['$gte' => 18], $adultsFilter['age']);

        $adults = HookedService::getMultiByCriteria($adultsFilter);
        $this->assertCount(2, $adults);

        $aliceFilter = HookedService::scope('named_alice');
        $aliceResults = HookedService::getMultiByCriteria($aliceFilter);
        $this->assertCount(1, $aliceResults);
        $this->assertEquals('Alice', $aliceResults[0]->getName());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testScopeWithMultipleNamesIsMerged(): void
    {
        $alice = new HookedModel();
        $alice->setName('Alice')->setAge(30);

        $bob = new HookedModel();
        $bob->setName('Bob')->setAge(15);

        HookedService::insertMulti([$alice, $bob]);

        // Merging 'adults' and 'named_alice' — only Alice is both an adult and named Alice
        $merged = HookedService::scope('adults', 'named_alice');
        $this->assertArrayHasKey('age', $merged);
        $this->assertArrayHasKey('name', $merged);

        $results = HookedService::getMultiByCriteria($merged);
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results[0]->getName());
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testScopeWithUnknownNameReturnsEmptyFilter(): void
    {
        $filter = HookedService::scope('nonexistent_scope');
        $this->assertIsArray($filter);
        $this->assertEmpty($filter);
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testMergeFiltersCombinesArrays(): void
    {
        $filter1 = ['age' => ['$gte' => 18]];
        $filter2 = ['name' => 'Alice'];
        $merged = HookedService::mergeFilters($filter1, $filter2);
        $this->assertArrayHasKey('age', $merged);
        $this->assertArrayHasKey('name', $merged);
        $this->assertEquals(['$gte' => 18], $merged['age']);
        $this->assertEquals('Alice', $merged['name']);
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testServiceWithNoScopesReturnsEmptyArray(): void
    {
        // MyService does not define any scopes
        $this->assertEmpty(MyService::scope('anything'));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function setUp(): void
    {
        HookedService::deleteMulti([]);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        HookedService::deleteMulti([]);
    }
}
