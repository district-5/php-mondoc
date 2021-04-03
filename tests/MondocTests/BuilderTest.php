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

namespace MondocTests;

use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\Example\MyModel;
use District5Tests\MondocTests\Example\MyService;
use District5Tests\MondocTests\MondocBaseTest;
use MongoDB\BSON\ObjectId;

/**
 * Class BuilderTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class BuilderTest extends MondocBaseTest
{
    public function testPersistAndQuery()
    {
        $this->initMongo();
        $this->mondoc->getCollection('test_model')->drop();

        $objectId = new ObjectId();

        $m = new MyModel();
        $m->setName('Foo');
        $m->setAge(1);
        $m->setPresetMongoId($objectId);
        $this->assertTrue($m->save());
        $this->assertEquals($objectId->__toString(), $m->getMongoIdString());

        $queryBuilderStringAndInt = new QueryBuilder();
        $equalsStringInt = new ValueEqualTo();
        $equalsStringInt->string('name', 'Foo');
        $equalsStringInt->integer('age', 1);
        $queryBuilderStringAndInt->addQueryPart($equalsStringInt);

        $queryBuilderObjectId = new QueryBuilder();
        $equalsObjectId = new ValueEqualTo();
        $equalsObjectId->mongoNative('_id', $objectId);
        $queryBuilderObjectId->addQueryPart($equalsObjectId);

        $getModel = MyService::getOneByQueryBuilder($queryBuilderStringAndInt);
        // @var $getModel MyModel
        $this->assertEquals($m->getMongoIdString(), $getModel->getMongoIdString());
        $this->assertEquals('Foo', $getModel->getName());
        $this->assertEquals(1, $getModel->getAge());

        $getModel = MyService::getOneByQueryBuilder($queryBuilderObjectId);
        // @var $getModel MyModel
        $this->assertEquals($m->getMongoIdString(), $getModel->getMongoIdString());
        $this->assertEquals('Foo', $getModel->getName());
        $this->assertEquals(1, $getModel->getAge());

        $getModels = MyService::getMultiByQueryBuilder($queryBuilderStringAndInt);
        // @var $getModels MyModel[]
        $this->assertCount(1, $getModels);
        $this->assertEquals($m->getMongoIdString(), $getModels[0]->getMongoIdString());
        $this->assertEquals('Foo', $getModels[0]->getName());
        $this->assertEquals(1, $getModels[0]->getAge());

        $getModels = MyService::getMultiByQueryBuilder($queryBuilderObjectId);
        // @var $getModels MyModel[]
        $this->assertCount(1, $getModels);
        $this->assertEquals($m->getMongoIdString(), $getModels[0]->getMongoIdString());
        $this->assertEquals('Foo', $getModels[0]->getName());
        $this->assertEquals(1, $getModels[0]->getAge());
    }
}
