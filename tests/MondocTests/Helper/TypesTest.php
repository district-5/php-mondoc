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

namespace District5Tests\MondocTests\Helper;

use DateTime;
use DateTimeInterface;
use District5\Date\Date;
use District5\Mondoc\Helper\MondocTypes;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\AllTypesModel;
use District5Tests\MondocTests\TestObjects\Service\AllTypesService;
use JsonException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use stdClass;

/**
 * Class TypesTest.
 *
 * @package District5Tests\MondocTests\Helper
 *
 * @internal
 */
class TypesTest extends MondocBaseTest
{
    public function testMongoIdCasting()
    {
        $stringId = '5f9b1b3b7f8b9b001f000000';
        $objectId = new ObjectId($stringId);

        $this->assertEquals($stringId, MondocTypes::objectIdToString($objectId));
        $this->assertEquals($objectId, MondocTypes::stringToObjectId($stringId));

        $this->assertEquals($stringId, MondocTypes::toObjectId($stringId)->__toString());
        $this->assertEquals($stringId, MondocTypes::toObjectId(['oid' => $stringId])->__toString());
        $this->assertEquals($stringId, MondocTypes::toObjectId(['$oid' => $stringId])->__toString());
    }

    public function testDateConversions()
    {
        $dateTime = new DateTime();
        $mongoRepresentation = MondocTypes::phpDateToMongoDateTime($dateTime);
        $this->assertInstanceOf(UTCDateTime::class, $mongoRepresentation);

        $backToPhp = MondocTypes::dateToPHPDateTime($mongoRepresentation);
        $this->assertEquals(
            $dateTime->format(DateTimeInterface::ATOM),
            $backToPhp->format(DateTimeInterface::ATOM)
        );

        $this->assertNull(MondocTypes::dateToPHPDateTime(1));
        $this->assertNull(MondocTypes::dateToPHPDateTime('1'));
        $this->assertNull(MondocTypes::dateToPHPDateTime(1.01));
        $this->assertNull(MondocTypes::dateToPHPDateTime(new stdClass()));
    }

    public function testBsonDocumentConversion()
    {
        $php = ['foo' => 'bar'];
        $bson = new BSONDocument($php);

        $phpRepresentation = MondocTypes::arrayToPhp($bson);
        $this->assertArrayHasKey('foo', $phpRepresentation);
        $this->assertEquals('bar', $phpRepresentation['foo']);

        $this->assertEquals(1, MondocTypes::arrayToPhp(1));
        $this->assertEquals('1', MondocTypes::arrayToPhp('1'));
        $this->assertEquals(1.01, MondocTypes::arrayToPhp(1.01));
        $this->assertEquals(new stdClass(), MondocTypes::arrayToPhp(new stdClass()));
    }

    public function testBsonArrayConversion()
    {
        $php = ['foo', 'bar'];
        $bson = new BSONArray($php);

        $phpRepresentation = MondocTypes::arrayToPhp($bson);
        $this->assertTrue(in_array('foo', $phpRepresentation));
        $this->assertTrue(in_array('bar', $phpRepresentation));
    }

    public function testJsonEncodableTypes()
    {
        $array = new BSONArray([1, 2]);
        $object = new BSONDocument(['foo' => 'bar']);
        $utcDateTime = new UTCDateTime();

        $convertedArray = MondocTypes::typeToJsonFriendly($array);
        $this->assertIsArray($convertedArray);
        $this->assertArrayHasKey(0, $convertedArray);
        $this->assertArrayHasKey(1, $convertedArray);

        $convertedObject = MondocTypes::typeToJsonFriendly($object);
        $this->assertIsArray($convertedObject);
        $this->assertArrayHasKey('foo', $convertedObject);

        $convertedDateTime = MondocTypes::typeToJsonFriendly($utcDateTime);
        $this->assertIsString($convertedDateTime);
        $this->assertIsNumeric($convertedDateTime);

        $this->assertEquals(1, MondocTypes::typeToJsonFriendly(1));
        $this->assertEquals('1', MondocTypes::typeToJsonFriendly('1'));
        $this->assertEquals(1.01, MondocTypes::typeToJsonFriendly(1.01));
        $this->assertEquals([], MondocTypes::typeToJsonFriendly(new stdClass()));

        $this->assertTrue(MondocTypes::typeToJsonFriendly(true));
        $this->assertFalse(MondocTypes::typeToJsonFriendly(false));
        $this->assertNull(MondocTypes::typeToJsonFriendly(null));
    }

    /**
     * @throws JsonException
     */
    public function testAsJsonEncodableOption()
    {
        $this->initMongo();

        $phpDate = Date::createYMDHISM(2021, 1, 1, 1, 1, 1, 1);
        $anId = new ObjectId();

        $m = new AllTypesModel();
        $m->setAnId($anId);
        $m->setString('string');
        $m->setInt(1);
        $m->setNull(null);
        $m->setFloat(1.01);
        $m->setBool(true);
        $m->setPhpDate($phpDate);
        $m->setMongoDate(new UTCDateTime($phpDate->getTimestamp() * 1000));
        $m->setPhpArray(
            [
                1,
                2,
                3,
                ['foo' => 'bar']
            ]
        );
        $m->setBsonArray(
            new BSONArray([
                1,
                2,
                3,
                ['foo' => 'bar']
            ])
        );

        $obj = new stdClass();
        $obj->foo = 'bar';
        $obj->array = [1, 2, 3];
        $m->setPhpObject($obj);
        $m->setBsonObject(
            new BSONDocument(
                [
                    'foo' => 'bar',
                    'array' => [1, 2, 3]
                ]
            )
        );

        $callableTest = function ($json) use ($anId, $phpDate) {
            $this->assertArrayHasKey('anId', $json);
            $this->assertArrayHasKey('string', $json);
            $this->assertArrayHasKey('int', $json);
            $this->assertArrayHasKey('null', $json);
            $this->assertArrayHasKey('float', $json);
            $this->assertArrayHasKey('bool', $json);
            $this->assertArrayHasKey('phpDate', $json);
            $this->assertArrayHasKey('mongoDate', $json);
            $this->assertArrayHasKey('phpArray', $json);
            $this->assertArrayHasKey('bsonArray', $json);
            $this->assertArrayHasKey('phpObject', $json);
            $this->assertArrayHasKey('bsonObject', $json);

            $this->assertIsString($json['anId']);
            $this->assertIsString($json['string']);
            $this->assertIsInt($json['int']);
            $this->assertNull($json['null']);
            $this->assertIsFloat($json['float']);
            $this->assertTrue($json['bool']);
            $this->assertIsString($json['phpDate']);
            $this->assertIsString($json['mongoDate']);
            $this->assertIsArray($json['phpArray']);
            $this->assertIsArray($json['bsonArray']);
            $this->assertIsArray($json['phpObject']);
            $this->assertIsArray($json['bsonObject']);

            $this->assertEquals('bar', $json['phpObject']['foo']);
            $this->assertEquals('bar', $json['bsonObject']['foo']);
            $this->assertEquals([1, 2, 3], $json['phpObject']['array']);
            $this->assertEquals([1, 2, 3], $json['bsonObject']['array']);
            $this->assertEquals([1, 2, 3, ['foo' => 'bar']], $json['phpArray']);
            $this->assertEquals([1, 2, 3, ['foo' => 'bar']], $json['bsonArray']);
            $this->assertEquals($phpDate->format('Uv'), $json['phpDate']);
            $this->assertEquals($phpDate->getTimestamp() * 1000, $json['mongoDate']);
            $this->assertEquals($anId->__toString(), $json['anId']);
            $this->assertEquals('string', $json['string']);
            $this->assertEquals(1, $json['int']);
            // $this->assertNull($json['null']); // already tested
            $this->assertEquals(1.01, $json['float']);
            // $this->assertTrue($json['bool']); // already tested
        };

        $json = $m->asJsonEncodableArray();
        $jsonWithoutId = $m->asJsonEncodableArray(['_id']);
        $callableTest($json);

        $m->save();

        $newM = AllTypesService::getById($m->getObjectId());
        $newJson = $newM->asJsonEncodableArray();
        $callableTest($newJson);

        $this->assertNotEquals(
            json_encode($json, JSON_THROW_ON_ERROR),
            json_encode($newJson, JSON_THROW_ON_ERROR)
        ); // Because _id is not in $json but is in $newJson

        $newJsonWithoutId = $newM->asJsonEncodableArray(['_id']);
        $this->assertEquals(
            json_encode($jsonWithoutId, JSON_THROW_ON_ERROR, 512),
            json_encode($newJsonWithoutId, JSON_THROW_ON_ERROR, 512)
        );
    }
}
