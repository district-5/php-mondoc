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
use District5Tests\MondocTests\TestObjects\Model\InvalidNestedModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\SingleAndMultiNestedModel;
use District5Tests\MondocTests\TestObjects\Model\HelperTraitsModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class ModelInflationTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class ModelInflationTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testInflationDeflation()
    {
        $data = [
            'name' => 'Foo',
            'age' => 2
        ];
        $inflated = MyModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals(2, $inflated->getAge());

        $anId = new ObjectId();
        $data = [
            'name' => 'Foo',
            'age' => 2,
            '_id' => $anId
        ];
        $inflated = MyModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals('Foo', $inflated->__get('name'));
        $this->assertEquals(2, $inflated->getAge());
        $this->assertEquals(2, $inflated->__get('age'));
        $this->assertEquals($anId->__toString(), $inflated->getObjectIdString());
    }

    /**
     * @throws MondocException
     * @throws MondocConfigConfigurationException
     */
    public function testInvalidClassMapInflation()
    {
        $data = ['a' => 'b'];
        $d = InvalidNestedModel::inflateSingleArray([
            'name' => 'foo',
            'invalidClass' => $data,
            'noField' => 'bar'
        ]);
        $this->assertArrayHasKey('noField', $d->asArray());
        $this->assertEquals('bar', $d->asArray()['noField']);
        $this->assertArrayHasKey('invalidClass', $d->asArray());
        $this->assertEquals(['a' => 'b'], $d->asArray()['invalidClass']);
        $this->assertArrayHasKey('name', $d->asArray());
        $this->assertEquals('foo', $d->asArray()['name']);
    }

    /**
     * @throws MondocException
     * @throws MondocConfigConfigurationException
     */
    public function testInflationWithInvalidDataReturnsEmptyModel()
    {
        $data = ['foo', 'bar', '_mondocUnmapped' => ['f']]; // all of this is invalid
        $m = SingleAndMultiNestedModel::inflateSingleArray($data);
        $this->assertArrayNotHasKey('name', $m->asArray()); // because name isn't set
        $this->assertEmpty($m->asArray()['foods']);
        $this->assertEmpty($m->asArray()['friends']);
        $this->assertEmpty($m->asArray()['food']['attributes']);
    }

    /**
     * @throws MondocException
     * @throws MondocConfigConfigurationException
     */
    public function testInflationDeflationOfVersionedModel()
    {
        $data = [
            'name' => 'Foo',
            '_v' => 2
        ];
        $inflated = HelperTraitsModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals(2, $inflated->getModelVersion());

        $anId = new ObjectId();
        $data = [
            'name' => 'Foo',
            '_v' => 2,
            '_id' => $anId
        ];
        $inflated = HelperTraitsModel::inflateSingleArray($data);
        $this->assertEquals('Foo', $inflated->getName());
        $this->assertEquals(2, $inflated->getModelVersion());
        $this->assertEquals($anId->__toString(), $inflated->getObjectIdString());
        $this->assertTrue($inflated->isModelVersionX(2));
        $this->assertFalse($inflated->isModelVersionX(1));
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @throws MondocServiceMapErrorException
     */
    public function testInflateMultipleArrays()
    {
        $data = [
            [
                'name' => 'Joe',
                'age' => 2
            ],
            [
                'name' => 'Jane',
                'age' => 4
            ],
            [
                'name' => 'Jack',
                'age' => 6
            ]
        ];
        $inflated = MyModel::inflateMultipleArrays($data);
        $this->assertCount(3, $inflated);
        $this->assertTrue(MyService::insertMulti($inflated));
        $this->assertTrue($inflated[0]->hasObjectId());
        $this->assertTrue($inflated[1]->hasObjectId());
        $this->assertTrue($inflated[2]->hasObjectId());
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
