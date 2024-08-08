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

namespace District5Tests\MondocTests;

use DateTime;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\NoServiceModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Class ModelFunctionalityTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class ModelFunctionalityTest extends MondocBaseTest
{
    /**
     * @throws MondocConfigConfigurationException
     */
    public function testGetCollection()
    {
        $collection = MyService::getCollection(MyService::class);
        $otherCollection = MyService::getCollection();
        $this->assertEquals(
            $collection->getCollectionName(),
            $otherCollection->getCollectionName()
        );
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testDateMethods()
    {
        $nowDate = new DateTime();
        $m = new DateModel();
        $m->setDate($nowDate);
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(false)->format('Y-m-d H:i:s'));
        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(true)->toDateTime()->format('Y-m-d H:i:s'));
        $this->assertFalse($m->hasObjectId());
        $m->save();
        $this->assertTrue($m->hasObjectId());
        $found = DateService::getById($m->getObjectId());
        /* @var $found DateModel */
        $this->assertEquals($m->getObjectIdString(), $found->getObjectIdString());
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $this->assertEquals($m->getDate(false)->format('Y-m-d H:i:s'), $found->getDate(false)->format('Y-m-d H:i:s'));
        $this->assertTrue($found->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testDeleteWorksAsExpected()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());
        $this->assertTrue($m->delete());
        $this->assertFalse($m->hasObjectId());

        $this->assertFalse($m->delete()); // already deleted, no ObjectId now
    }

    public function testBasicModelMethods()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');
        $this->assertEquals(2, $m->getAge());
        $this->assertEquals('Joe', $m->getName());

        $array = $m->asArray();
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('name', $array);

        $bsonArray = new BSONArray(['foo' => 'bar']);
        $cleanArray = $m->getArrayFromBson($bsonArray);
        $this->assertIsArray($cleanArray);
        $this->assertArrayHasKey('foo', $cleanArray);
        $this->assertEquals('bar', $cleanArray['foo']);

        $bsonDocument = new BSONDocument(['foo' => 'bar']);
        $cleanArray = $m->getArrayFromBson($bsonDocument);
        $this->assertIsArray($cleanArray);
        $this->assertArrayHasKey('foo', $cleanArray);
        $this->assertEquals('bar', $cleanArray['foo']);

        $dirty = $m->getDirty();
        $this->assertTrue(in_array('age', $dirty));
        $this->assertTrue(in_array('name', $dirty));
        $this->assertEquals(2, $array['age']);
        $this->assertEquals('Joe', $array['name']);

        $this->assertEquals(['foo' => 'bar'], $m->getArrayFromArray(['foo' => 'bar']));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testModelHasAndDoesNotHaveIds()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());

        // not been saved yet
        $this->assertFalse($m->hasObjectId());

        // save the model
        $this->assertTrue($m->save());

        // ensure it has a mongo id now
        $this->assertTrue($m->hasObjectId());

        // delete the model
        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testGetWhereEqualOrNotEqual()
    {
        MyService::deleteMulti([]);
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());
        $m->save();
        $this->assertCount(1, MyService::getMultiWhereKeyEqualsValue('age', 2));
        $this->assertCount(0, MyService::getMultiWhereKeyDoesNotEqualValue('age', 2));

        $this->assertEquals($m->getObjectIdString(), MyService::getOneWhereKeyEqualsValue('age', 2)->getObjectIdString());
        $this->assertNull(MyService::getOneWhereKeyDoesNotEqualValue('age', 2));

        // delete the model
        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testModelExistence()
    {
        $m = new MyModel();
        $m->setAge(12345);
        $m->setName('existence');
        $this->assertTrue($m->save());

        $this->assertTrue(MyService::exists(['age' => 12345]));
        $this->assertTrue(MyService::exists(['name' => 'existence']));

        $this->assertTrue(MyService::exists(['age' => 12345], ['sort' => ['age' => 1]]));
        $this->assertTrue(MyService::exists(['name' => 'existence'], ['limit' => 1]));

        $this->assertFalse(MyService::exists(['age' => 9876]));
        $this->assertFalse(MyService::exists(['name' => 'nope']));

        $queryBuilderCorrectAge = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->integer('age', 12345));
        $queryBuilderCorrectName = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->string('name', 'existence'));

        $queryBuilderIncorrectAge = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->integer('age', 9876));
        $queryBuilderIncorrectName = QueryBuilder::get()->addQueryPart(ValueEqualTo::get()->string('name', 'nope'));

        $this->assertTrue(MyService::existsWithQueryBuilder($queryBuilderCorrectAge));
        $this->assertTrue(MyService::existsWithQueryBuilder($queryBuilderCorrectName));

        $this->assertFalse(MyService::existsWithQueryBuilder($queryBuilderIncorrectAge));
        $this->assertFalse(MyService::existsWithQueryBuilder($queryBuilderIncorrectName));

        // delete the model
        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testGetByIdWhereEmptyOrInvalidIds()
    {
        $value = MyService::getById('foo');
        $this->assertNull($value);
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testGetByIdsWhereEmptyOrInvalidIds()
    {
        $value = MyService::getByIds([]);
        $this->assertIsArray($value);
        $this->assertEmpty($value);

        $value = MyService::getByIds(['foo']);
        $this->assertIsArray($value);
        $this->assertEmpty($value);
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testPersistAndQuery()
    {

        $this->assertEquals(0, MyService::countAll([]));

        $name = 'Joe ' . $this->getUniqueKey();
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($name);

        // save the model
        $this->assertTrue($m->save());

        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::inc($m->getObjectId(), 'age', 2));
        $this->assertEquals(4, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateOne(['_id' => $m->getObjectId()], ['$set' => ['age' => 2]]));
        $this->assertEquals(2, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateOne(['_id' => $m->getObjectId()], ['$set' => ['age' => 4]]));
        $this->assertEquals(4, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::dec($m->getObjectId(), 'age', 2));
        $this->assertEquals(2, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getAverage('age', ['name' => $m->getName()]));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateMany(['age' => 2], ['$set' => ['age' => 123]]));
        $this->assertEquals(123, MyService::getById($m->getObjectId())->getAge());

        $queryBuilder = MyService::getQueryBuilder();
        $queryBuilder->addQueryPart(ValueEqualTo::get()->integer('age', 123));
        $this->assertTrue(MyService::updateManyByQueryBuilder($queryBuilder, ['$set' => ['age' => 456]]));
        $this->assertEquals(456, MyService::getById($m->getObjectId())->getAge());

        $queryBuilderTwo = MyService::getQueryBuilder();
        $queryBuilderTwo->addQueryPart(ValueEqualTo::get()->integer('age', 456));
        $this->assertTrue(MyService::updateOneByQueryBuilder($queryBuilderTwo, ['$set' => ['age' => 789]]));
        $this->assertEquals(789, MyService::getById($m->getObjectId())->getAge());

        // Get the ID
        $theId = $m->getObjectIdString();

        unset($m);

        $this->assertEquals(1, MyService::countAll(['name' => $name]));
        $builder = MyService::getQueryBuilder();
        $equal = ValueEqualTo::get();
        $equal->string('name', $name);
        $builder->addQueryPart($equal);
        $this->assertEquals(1, MyService::countAllByQueryBuilder($builder));

        $find = MyService::getOneByCriteria(['name' => $name]);
        /* @var $find MyModel */
        $this->assertNotNull($find);
        $this->assertInstanceOf(MyModel::class, $find);

        $this->assertEquals($name, $find->getName());

        // ensure it has a mongo id now
        $this->assertTrue($find->hasObjectId());
        $this->assertEquals($theId, $find->getObjectIdString());

        unset($find);

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);
        $this->assertNotNull($multi[0]);
        $this->assertInstanceOf(MyModel::class, $multi[0]);

        $this->assertEquals($name, $multi[0]->getName());

        $multiByIds = MyService::getByIds([$theId]);
        $this->assertCount(1, $multiByIds);
        $this->assertNotNull($multiByIds[0]);
        $this->assertInstanceOf(MyModel::class, $multiByIds[0]);

        // ensure it has a mongo id now
        $this->assertTrue($multi[0]->hasObjectId());
        $this->assertEquals($theId, $multi[0]->getObjectIdString());

        unset($multi);

        $multi = MyService::getMultiByCriteria(['name' => $name]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $time = microtime(false);
        $multi[0]->setName('joe-bloggs ' . $time);
        $this->assertTrue($multi[0]->save());
        unset($multi);

        $multi = MyService::getMultiByCriteria(['name' => 'joe-bloggs ' . $time]);
        /* @var $multi MyModel[] */
        $this->assertCount(1, $multi);

        // delete the model
        $this->assertTrue($multi[0]->delete());

        unset($multi);
    }

    public function testPropertyExclusionsWorkCorrectly()
    {
        $single = new NoServiceModel();
        $this->assertTrue($single->exposeIsPropertyExcludedSingle('_mondocObjectId')); // _mondocObjectId is excluded
        $this->assertFalse($single->exposeIsPropertyExcludedSingle('thisIsOk')); // thisIsOk is not excluded
        $this->assertTrue($single->exposeIsPropertyExcludedArray(['thisIsOk', '_mondocObjectId'])); // _mondocObjectId is excluded
        $this->assertFalse($single->exposeIsPropertyExcludedArray(['thisIsOk', 'thisToo'])); // neither are excluded
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::deleteMulti([]);
    }
}
