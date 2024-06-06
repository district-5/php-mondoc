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

namespace District5Tests\MondocTests\FunctionalityParts;

use District5\MondocBuilder\QueryBuilder;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use InvalidArgumentException;
use MongoDB\BSON\ObjectId;

/**
 * Class PaginationTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class PaginationTest extends MondocBaseTest
{
    public function testPagination()
    {
        MyService::deleteMulti([]);

        $builder = new QueryBuilder();

        $this->assertEquals(0, MyService::countAll([]));
        $this->assertEquals(0, MyService::countAllByQueryBuilder($builder));

        $ages = [2 => 'Joe', 4 => 'Joe', 6 => 'Jane'];
        $idsSaved = [];
        foreach ($ages as $age => $name) {
            $m = new MyModel();
            $m->setAge($age);
            $m->setName($name);
            $this->assertTrue($m->save());
            $idsSaved[] = $m->getObjectId();
        }

        $paginator = MyService::getPaginationHelper(1, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());

        $ids = [];
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getObjectIdString(), $ids));
        $ids[] = $results[0]->getObjectIdString();

        $paginator = MyService::getPaginationHelper(2, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getObjectIdString(), $ids));
        $ids[] = $results[0]->getObjectIdString();

        $paginator = MyService::getPaginationHelper(3, 1, []);
        $this->assertEquals(3, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, [], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getObjectIdString(), $ids));

        $paginator = MyService::getPaginationHelper(1, 1, ['name' => 'Joe']);
        $this->assertEquals(2, $paginator->getTotalPages());

        $ids = [];
        $results = MyService::getPage($paginator, ['name' => 'Joe'], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getObjectIdString(), $ids));
        $ids[] = $results[0]->getObjectIdString();

        $paginator = MyService::getPaginationHelper(2, 1, ['name' => 'Joe']);
        $this->assertEquals(2, $paginator->getTotalPages());
        $results = MyService::getPage($paginator, ['name' => 'Joe'], 'name', 1);
        $this->assertCount(1, $results);
        $this->assertFalse(in_array($results[0]->getObjectIdString(), $ids));

        $paginator = MyService::getPaginationHelperForObjectIdPagination(1, []);
        $firstPage = MyService::getPageByByObjectIdPagination($paginator, null, 1, []);
        $this->assertCount(1, $firstPage);
        $this->assertEquals('Joe', $firstPage[0]->getName());

        $firstPage = MyService::getPageByByObjectIdPagination($paginator, null, -1, []);
        $this->assertCount(1, $firstPage);
        $this->assertEquals('Jane', $firstPage[0]->getName());

        $paginator = MyService::getPaginationHelperForObjectIdPagination(1, []);
        $this->assertEquals($idsSaved[1]->__toString(), MyService::getPageByByObjectIdPagination($paginator, $idsSaved[0], 1, [])[0]->getObjectIdString());
        $this->assertEquals($idsSaved[1]->__toString(), MyService::getPageByByObjectIdPagination($paginator, $idsSaved[2], -1, [])[0]->getObjectIdString());
    }

    public function testInvalidSortDirectionForPaginationByObjectId()
    {
        $this->expectException(InvalidArgumentException::class);
        $paginator = MyService::getPaginationHelperForObjectIdPagination(1, []);
        MyService::getPageByByObjectIdPagination($paginator, new ObjectId(), 2, []); // invalid sort direction
    }
}
