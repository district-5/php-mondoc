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
use District5Tests\MondocTests\TestObjects\Model\FieldAliasTestModel;
use District5Tests\MondocTests\TestObjects\Service\FieldAliasTestService;
use MongoDB\BSON\ObjectId;
use ReflectionClass;

/**
 * Class FieldAliasTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class FieldAliasTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testFieldMap()
    {
        FieldAliasTestService::deleteMulti([]);

        $m = new FieldAliasTestModel();
        $m->setName('John');
        $m->setCity('London');
        $m->setAge(25);
        $attributes = $m->getAttributes();
        $attributes->setHairColor('brown');
        $attributes->setHairDescription('Short hair, with a slight curl');
        $attributes->setHairLength('short');

        $array = $m->asArray();
        $this->assertArrayHasKey('n', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertArrayHasKey('a', $array);
        $this->assertArrayNotHasKey('age', $array);
        $this->assertTrue($m->save());

        $second = FieldAliasTestService::getById($m->getObjectId());
        $this->assertInstanceOf(FieldAliasTestModel::class, $second);
        $this->assertEquals('John', $second->getName());
        $this->assertEquals('London', $second->getCity());
        $this->assertEquals(25, $second->getAge());

        $this->assertObjectNotHasProperty('attributes', $second->getOriginalBsonDocument());
        $this->assertObjectHasProperty('attr', $second->getOriginalBsonDocument());

        $this->assertObjectNotHasProperty('hairColor', $second->getOriginalBsonDocument()->attr);
        $this->assertObjectHasProperty('hc', $second->getOriginalBsonDocument()->attr);
        $this->assertEquals('brown', $second->getOriginalBsonDocument()->attr->hc);

        $this->assertObjectNotHasProperty('hairDescription', $second->getOriginalBsonDocument()->attr);
        $this->assertObjectHasProperty('hd', $second->getOriginalBsonDocument()->attr);
        $this->assertEquals('Short hair, with a slight curl', $second->getOriginalBsonDocument()->attr->hd);

        $this->assertObjectHasProperty('hairLength', $second->getOriginalBsonDocument()->attr);
        $this->assertEquals('short', $second->getOriginalBsonDocument()->attr->hairLength);

        $second->setName('Jane');
        $second->setCity('Paris');
        $second->setAge(30);
        $this->assertTrue($second->save());

        $third = FieldAliasTestService::getById($m->getObjectId());
        $this->assertInstanceOf(FieldAliasTestModel::class, $third);
        $this->assertEquals('Jane', $third->getName());
        $this->assertEquals('Paris', $third->getCity());
        $this->assertEquals(30, $third->getAge());

        $this->assertTrue($third->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testFieldMapNested()
    {
        FieldAliasTestService::deleteMulti([]);

        $thisId = new ObjectId();
        $arrayData = [
            'n' => 'John',
            'a' => 25,
            'city' => 'London',
            'attr' => [
                'hc' => 'brown',
                'hd' => 'Short hair, with a slight curl',
                'hairLength' => 'short'
            ],
            'multiAttributes' => [
                [
                    'hc' => 'brown',
                    'hd' => 'Short hair, with a slight curl',
                    'hairLength' => 'short'
                ],
                [
                    'hc' => 'red',
                    'hd' => 'Long hair, with a slight wave',
                    'hairLength' => 'long'
                ]
            ]
        ];
        $m = FieldAliasTestModel::inflateSingleArray($arrayData);
        $this->assertInstanceOf(FieldAliasTestModel::class, $m);
        $this->assertEquals('John', $m->getName());
        $this->assertEquals('London', $m->getCity());
        $this->assertEquals(25, $m->getAge());
        $attributes = $m->getAttributes();
        $this->assertEquals('brown', $attributes->getHairColor());
        $this->assertEquals('Short hair, with a slight curl', $attributes->getHairDescription());
        $this->assertEquals('short', $attributes->getHairLength());
        $this->assertCount(2, $m->getMultiAttributes());
        $this->assertEquals('brown', $m->getMultiAttributes()[0]->getHairColor());
        $this->assertEquals('Short hair, with a slight curl', $m->getMultiAttributes()[0]->getHairDescription());
        $this->assertEquals('short', $m->getMultiAttributes()[0]->getHairLength());
        $this->assertEquals('red', $m->getMultiAttributes()[1]->getHairColor());
        $this->assertEquals('Long hair, with a slight wave', $m->getMultiAttributes()[1]->getHairDescription());
        $this->assertEquals('long', $m->getMultiAttributes()[1]->getHairLength());
        $this->assertTrue($m->save());

        $second = FieldAliasTestService::getById($m->getObjectId());
        $this->assertInstanceOf(FieldAliasTestModel::class, $second);
        $this->assertEquals('John', $second->getName());
        $this->assertEquals('London', $second->getCity());
        $this->assertEquals(25, $second->getAge());
        $attributes = $second->getAttributes();
        $this->assertEquals('brown', $attributes->getHairColor());
        $this->assertEquals('Short hair, with a slight curl', $attributes->getHairDescription());
        $this->assertEquals('short', $attributes->getHairLength());
        $this->assertCount(2, $second->getMultiAttributes());
        $this->assertEquals('brown', $second->getMultiAttributes()[0]->getHairColor());
        $this->assertEquals('Short hair, with a slight curl', $second->getMultiAttributes()[0]->getHairDescription());
        $this->assertEquals('short', $second->getMultiAttributes()[0]->getHairLength());
        $this->assertEquals('red', $second->getMultiAttributes()[1]->getHairColor());
        $this->assertEquals('Long hair, with a slight wave', $second->getMultiAttributes()[1]->getHairDescription());
        $this->assertEquals('long', $second->getMultiAttributes()[1]->getHairLength());

        $second->setName('Jane');
        $second->setCity('Paris');
        $second->setAge(30);
        $attributes = $second->getAttributes();
        $attributes->setHairColor('blonde');
        $attributes->setHairDescription('Long hair, with a slight wave');
        $attributes->setHairLength('long');
        $second->setAttributes($attributes);
        $multiAttributes = $second->getMultiAttributes();
        $multiAttributes[0]->setHairColor('blonde');
        $multiAttributes[0]->setHairDescription('Long hair, with a slight wave');
        $multiAttributes[0]->setHairLength('long');
        $multiAttributes[1]->setHairColor('brown');
        $multiAttributes[1]->setHairDescription('Short hair, with a slight curl');
        $multiAttributes[1]->setHairLength('short');
        $second->setMultiAttributes($multiAttributes);
        $this->assertTrue($second->save());

        $third = FieldAliasTestService::getById($m->getObjectId());
        $this->assertInstanceOf(FieldAliasTestModel::class, $third);
        $this->assertEquals('Jane', $third->getName());
        $this->assertEquals('Paris', $third->getCity());
        $this->assertEquals(30, $third->getAge());
        $attributes = $third->getAttributes();
        $this->assertEquals('blonde', $attributes->getHairColor());
        $this->assertEquals('Long hair, with a slight wave', $attributes->getHairDescription());
        $this->assertEquals('long', $attributes->getHairLength());
        $this->assertCount(2, $third->getMultiAttributes());
        $this->assertEquals('blonde', $third->getMultiAttributes()[0]->getHairColor());
        $this->assertEquals('Long hair, with a slight wave', $third->getMultiAttributes()[0]->getHairDescription());
        $this->assertEquals('long', $third->getMultiAttributes()[0]->getHairLength());
        $this->assertEquals('brown', $third->getMultiAttributes()[1]->getHairColor());
        $this->assertEquals('Short hair, with a slight curl', $third->getMultiAttributes()[1]->getHairDescription());
        $this->assertEquals('short', $third->getMultiAttributes()[1]->getHairLength());
        $this->assertTrue($third->delete());
    }

    public function testFieldAliasMapMethodsReturnExpected()
    {
        $m = new FieldAliasTestModel();
        $reflection = new ReflectionClass($m);
        $property = $reflection->getProperty('mondocFieldAliases');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);
        $property->setValue($m, [
            'n' => 'name',
            'a' => 'age',
            'attr' => 'attributes',
            'foo' => 'bar'
        ]);

        $this->assertEquals('n', $m->getFieldAliasMapRemoteName('name'));
        $this->assertEquals('n', $m->getFieldAliasMapRemoteName('n'));
        $this->assertEquals('name', $m->getFieldAliasMapLocalName('n'));
        $this->assertEquals('name', $m->getFieldAliasMapLocalName('name'));

        $this->assertEquals('a', $m->getFieldAliasMapRemoteName('age'));
        $this->assertEquals('a', $m->getFieldAliasMapRemoteName('a'));
        $this->assertEquals('age', $m->getFieldAliasMapLocalName('a'));
        $this->assertEquals('age', $m->getFieldAliasMapLocalName('age'));

        $this->assertEquals('attr', $m->getFieldAliasMapRemoteName('attributes'));
        $this->assertEquals('attr', $m->getFieldAliasMapRemoteName('attr'));
        $this->assertEquals('attributes', $m->getFieldAliasMapLocalName('attr'));
        $this->assertEquals('attributes', $m->getFieldAliasMapLocalName('attributes'));

        $this->assertEquals('foo', $m->getFieldAliasMapRemoteName('bar'));
        $this->assertEquals('foo', $m->getFieldAliasMapRemoteName('foo'));
        $this->assertEquals('bar', $m->getFieldAliasMapLocalName('foo'));
        $this->assertEquals('bar', $m->getFieldAliasMapLocalName('bar'));
    }

    public function testDeprecatedFieldAliasMapMethod()
    {
        $m = new FieldAliasTestModel();
        $reflection = new ReflectionClass($m);
        $property = $reflection->getProperty('mondocFieldAliases');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);
        $property->setValue($m, [
            'n' => 'name',
            'a' => 'age',
            'attr' => 'attributes',
            'foo' => 'bar'
        ]);

        $this->assertEquals('n', $m->getFieldAliasSingleMap('name', true));
        $this->assertEquals('n', $m->getFieldAliasSingleMap('n', true));
        $this->assertEquals('name', $m->getFieldAliasSingleMap('n', false));
        $this->assertEquals('name', $m->getFieldAliasSingleMap('name', false));

        $this->assertEquals('a', $m->getFieldAliasSingleMap('age', true));
        $this->assertEquals('a', $m->getFieldAliasSingleMap('a', true));
        $this->assertEquals('age', $m->getFieldAliasSingleMap('a', false));
        $this->assertEquals('age', $m->getFieldAliasSingleMap('age', false));

        $this->assertEquals('attr', $m->getFieldAliasSingleMap('attributes', true));
        $this->assertEquals('attr', $m->getFieldAliasSingleMap('attr', true));
        $this->assertEquals('attributes', $m->getFieldAliasSingleMap('attr', false));
        $this->assertEquals('attributes', $m->getFieldAliasSingleMap('attributes', false));

        $this->assertEquals('foo', $m->getFieldAliasSingleMap('bar', true));
        $this->assertEquals('foo', $m->getFieldAliasSingleMap('foo', true));
        $this->assertEquals('bar', $m->getFieldAliasSingleMap('foo', false));
        $this->assertEquals('bar', $m->getFieldAliasSingleMap('bar', false));
    }
}
