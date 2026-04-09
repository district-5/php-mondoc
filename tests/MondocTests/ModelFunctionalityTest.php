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

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\NoServiceModel;
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
class ModelFunctionalityTest extends MondocBaseTestAbstract
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
     */
    public function testGetServiceFromModelAndModelFromService()
    {
        $model = MyService::getMondocModelClass();

        $service = MyModel::getMondocServiceClass();
        /* @var $service MondocAbstractService (it's actually a string) */
        $modelFromService = $service::getMondocModelClass();
        /* @var $modelFromService MondocAbstractModel (it's actually a string) */
        $this->assertEquals($model, $modelFromService);

        $this->assertEquals($service, $modelFromService::getMondocServiceClass());
    }

    /**
     * @return void
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public function testBasicModelMethods()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');
        $this->assertEquals(2, $m->getAge());
        $this->assertEquals('Joe', $m->getName());

        $array = $m->asArray(false);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertEquals(2, $array['age']);
        $this->assertEquals('Joe', $array['name']);

        $dirty = $m->getDirty();
        $this->assertTrue(in_array('age', $dirty));
        $this->assertTrue(in_array('name', $dirty));

        $bsonArray = new BSONArray(['foo' => 'bar']);
        $fromBsonArray = $m->getArrayFromBson($bsonArray);
        $this->assertIsArray($fromBsonArray);
        $this->assertArrayHasKey('foo', $fromBsonArray);
        $this->assertEquals('bar', $fromBsonArray['foo']);

        $bsonDocument = new BSONDocument(['foo' => 'bar']);
        $fromBsonDocument = $m->getArrayFromBson($bsonDocument);
        $this->assertIsArray($fromBsonDocument);
        $this->assertArrayHasKey('foo', $fromBsonDocument);
        $this->assertEquals('bar', $fromBsonDocument['foo']);

        $this->assertEquals(['foo' => 'bar'], $m->getArrayFromArray(['foo' => 'bar']));
    }

    public function testPropertyExclusionsWorkCorrectly()
    {
        $single = new NoServiceModel();
        $this->assertTrue($single->exposeIsPropertyExcludedSingle('_mondocObjectId'));
        $this->assertFalse($single->exposeIsPropertyExcludedSingle('thisIsOk'));
        $this->assertTrue($single->exposeIsPropertyExcludedArray(['thisIsOk', '_mondocObjectId']));
        $this->assertFalse($single->exposeIsPropertyExcludedArray(['thisIsOk', 'thisToo']));
    }
}
