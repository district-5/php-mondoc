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
use District5\Mondoc\Exception\MondocEncryptionException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\Model\FieldAliasTestModel;
use District5Tests\MondocTests\TestObjects\Service\FieldAliasTestService;
use MongoDB\BSON\ObjectId;

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
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     */
    public function testFieldMap()
    {
        FieldAliasTestService::deleteMulti([]);

        $m = new FieldAliasTestModel();
        $m->setName('John');
        $m->setCity('London');
        $m->setAge(25);

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
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
     * @throws MondocEncryptionException
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
}
