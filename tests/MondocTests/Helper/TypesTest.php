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
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\MondocTypes;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\AllTypesModel;
use District5Tests\MondocTests\TestObjects\Model\NoServiceModel;
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
    public function testDateToPHPDateTime()
    {
        $phpDateTime = Date::createYMDHISM(2021, 1, 1, 1, 1, 1, 1);
        $utcDateTime = new UTCDateTime($phpDateTime->getTimestamp() * 1000);

        $converted = MondocTypes::dateToPHPDateTime($phpDateTime);
        $this->assertEquals($phpDateTime->format(DateTimeInterface::ATOM), $converted->format(DateTimeInterface::ATOM));

        $converted = MondocTypes::dateToPHPDateTime($utcDateTime);
        $this->assertEquals($phpDateTime->format(DateTimeInterface::ATOM), $converted->format(DateTimeInterface::ATOM));

        $this->assertNull(MondocTypes::dateToPHPDateTime(1));
        $this->assertNull(MondocTypes::dateToPHPDateTime(new stdClass()));
    }

    public function testPhpDateToMongoDateTime()
    {
        $phpDateTime = Date::createYMDHISM(2021, 1, 1, 1, 1, 1, 1);
        $utcDateTime = new UTCDateTime($phpDateTime->getTimestamp() * 1000);

        $converted = MondocTypes::phpDateToMongoDateTime($phpDateTime);
        $this->assertEquals($utcDateTime, $converted);

        $converted = MondocTypes::phpDateToMongoDateTime($utcDateTime);
        $this->assertEquals($utcDateTime, $converted);

        $this->assertNull(MondocTypes::phpDateToMongoDateTime(1));
        $this->assertNull(MondocTypes::phpDateToMongoDateTime(new stdClass()));
    }

    public function testConvertingAnyArrayItemToPhp()
    {
        $array = ['foo' => 'bar'];
        $bsonArray = new BSONArray(array_merge([], $array));
        $bsonDoc = new BSONDocument(array_merge([], $array));

        $this->assertEquals($array, MondocTypes::arrayToPhp($array));
        $this->assertEquals($array, MondocTypes::arrayToPhp($bsonArray));
        $this->assertEquals($array, MondocTypes::arrayToPhp($bsonDoc));
        $this->assertEquals(1, MondocTypes::arrayToPhp(1)); // non array items should just return themselves
    }

    public function testConvertingItemsToObjectId()
    {
        $validStringId = '5f9b1b3b7f8b9b001f000000';
        $objectId = new ObjectId($validStringId);

        $this->assertEquals($validStringId, MondocTypes::toObjectId($validStringId)->__toString());
        $this->assertEquals($validStringId, MondocTypes::toObjectId(['oid' => $validStringId])->__toString());
        $this->assertEquals($validStringId, MondocTypes::toObjectId(['$oid' => $validStringId])->__toString());
        $this->assertEquals($objectId->__toString(), MondocTypes::toObjectId($objectId)->__toString());

        $this->assertNull(MondocTypes::toObjectId(1));
        $this->assertNull(MondocTypes::toObjectId('1'));
        $this->assertNull(MondocTypes::toObjectId(1.01));
        $this->assertNull(MondocTypes::toObjectId(['foo' => 'bar']));
        $this->assertNull(MondocTypes::toObjectId(null));
    }

    public function testConvertingObjectIdString()
    {
        $stringId = '5f9b1b3b7f8b9b001f000000';
        $objectId = new ObjectId($stringId);

        $this->assertEquals($stringId, MondocTypes::objectIdToString($objectId));
    }

    public function testMongoIdCasting()
    {
        $stringId = '5f9b1b3b7f8b9b001f000000';
        $objectId = new ObjectId($stringId);

        $this->assertEquals($stringId, MondocTypes::objectIdToString($objectId));

        $this->assertEquals($stringId, MondocTypes::toObjectId($stringId)->__toString());
        $this->assertEquals($stringId, MondocTypes::toObjectId(['oid' => $stringId])->__toString());
        $this->assertEquals($stringId, MondocTypes::toObjectId(['$oid' => $stringId])->__toString());

        $this->assertNull(MondocTypes::toObjectId(1));
        $this->assertNull(MondocTypes::toObjectId('1'));
        $this->assertNull(MondocTypes::toObjectId(1.01));
        $this->assertNull(MondocTypes::toObjectId(['foo' => 'bar']));
    }

    public function testValidDateConversions()
    {
        $dateTime = new DateTime();
        $mongoRepresentation = MondocTypes::phpDateToMongoDateTime($dateTime);
        $this->assertInstanceOf(UTCDateTime::class, $mongoRepresentation);

        $backToPhp = MondocTypes::dateToPHPDateTime($mongoRepresentation);
        $this->assertEquals(
            $dateTime->format(DateTimeInterface::ATOM),
            $backToPhp->format(DateTimeInterface::ATOM)
        );

        $this->assertEquals($dateTime->getTimestamp(), MondocTypes::dateToPHPDateTime($dateTime)->getTimestamp());

        $this->assertNull(MondocTypes::dateToPHPDateTime(1));
        $this->assertNull(MondocTypes::dateToPHPDateTime('1'));
        $this->assertNull(MondocTypes::dateToPHPDateTime(1.01));
        $this->assertNull(MondocTypes::dateToPHPDateTime(new stdClass()));
    }

    public function testInvalidDateConversions()
    {
        $this->assertNull(MondocTypes::phpDateToMongoDateTime(1));
        $this->assertNull(MondocTypes::phpDateToMongoDateTime('1'));
        $this->assertNull(MondocTypes::phpDateToMongoDateTime(1.01));
        $this->assertNull(MondocTypes::phpDateToMongoDateTime(new stdClass()));
        $dateTime = new DateTime();
        $mongoVersion = Date::mongo()->convertTo($dateTime);
        $newMongoVersion = MondocTypes::phpDateToMongoDateTime($mongoVersion);
        $this->assertEquals($mongoVersion, $newMongoVersion);
    }

    public function testArrayToPHPArray()
    {
        $array = ['foo' => 'bar'];
        $bsonArray = new BSONArray(array_merge([], $array));
        $bsonDoc = new BSONDocument(array_merge([], $array));

        $this->assertEquals($array, MondocTypes::arrayToPhp($array));
        $this->assertEquals($array, MondocTypes::arrayToPhp($bsonArray));
        $this->assertEquals($array, MondocTypes::arrayToPhp($bsonDoc));
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
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', '2021-01-01 01:01:01');
        $stdClass = new stdClass();
        $stdClass->foo = 'bar';

        $convertedArray = MondocTypes::typeToJsonFriendly($array);
        $this->assertIsArray($convertedArray);
        $this->assertArrayHasKey(0, $convertedArray);
        $this->assertArrayHasKey(1, $convertedArray);

        $convertedObject = MondocTypes::typeToJsonFriendly($object);
        $this->assertIsArray($convertedObject);
        $this->assertArrayHasKey('foo', $convertedObject);

        $convertedUtcDateTime = MondocTypes::typeToJsonFriendly($utcDateTime);
        $this->assertIsString($convertedUtcDateTime);
        $this->assertIsNumeric($convertedUtcDateTime);

        $convertedDateTime = MondocTypes::typeToJsonFriendly($dateTime);
        $this->assertIsString($convertedDateTime);
        $this->assertIsNumeric($convertedDateTime);

        $this->assertEquals(1, MondocTypes::typeToJsonFriendly(1));
        $this->assertEquals('1', MondocTypes::typeToJsonFriendly('1'));
        $this->assertEquals(1.01, MondocTypes::typeToJsonFriendly(1.01));
        $this->assertEquals([], MondocTypes::typeToJsonFriendly(new stdClass()));

        $bsonDoc = new BSONDocument(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], MondocTypes::typeToJsonFriendly($bsonDoc));
        $bsonArray = new BSONArray([1, 2, 3]);
        $this->assertEquals([1, 2, 3], MondocTypes::typeToJsonFriendly($bsonArray));

        $this->assertEquals(['foo' => 'bar'], MondocTypes::typeToJsonFriendly($stdClass));

        $foo = new class {
            public string $foo = 'bar';
        };

        $this->assertNull(MondocTypes::typeToJsonFriendly($foo));

        $this->assertTrue(MondocTypes::typeToJsonFriendly(true));
        $this->assertFalse(MondocTypes::typeToJsonFriendly(false));
        $this->assertNull(MondocTypes::typeToJsonFriendly(null));
    }

    /**
     * @throws JsonException
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testAsJsonEncodableOption()
    {
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

    public function testConvertArrayOfObjectIds()
    {
        $ids = [
            new ObjectId(),
            new ObjectId(),
            new ObjectId(),
            '5f9b1b3b7f8b9b001f000000',
            '5f9b1b3b7f8b9b001f000001',
            '5f9b1b3b7f8b9b001f000002',
            'invalid'
        ];

        $a = new AllTypesModel();
        $converted = $a->exposeConvertArrayOfMongoIdsToMongoIds($ids);
        $this->assertIsArray($converted);
        $this->assertCount(6, $converted); // 7th is invalid
        $this->assertEquals($ids[0]->__toString(), $converted[0]->__toString());
        $this->assertEquals($ids[1]->__toString(), $converted[1]->__toString());
        $this->assertEquals($ids[2]->__toString(), $converted[2]->__toString());
        $this->assertEquals($ids[3], $converted[3]->__toString());
        $this->assertEquals($ids[4], $converted[4]->__toString());
        $this->assertEquals($ids[5], $converted[5]->__toString());
    }

    public function testDirty()
    {
        $myModel = new NoServiceModel();
        $this->assertFalse($myModel->isDirty(null));
        $this->assertFalse($myModel->isDirty('name'));
        $this->assertFalse($myModel->isDirty('blah'));
        $myModel->setName('foo');
        $this->assertTrue($myModel->isDirty(null));
        $this->assertTrue($myModel->isDirty('name'));
        $this->assertFalse($myModel->isDirty('blah'));
    }

    public function testDeduplicationOfArrayOfObjectIds()
    {
        $deDuplicatedObjectIds = MondocTypes::deduplicateArrayOfObjectIds(
            [new ObjectId(), new ObjectId(), new ObjectId(), new ObjectId('61dfee5591efcf44e023d692'), new ObjectId('61dfee5591efcf44e023d692')] // 4 unique
        );
        $this->assertCount(4, $deDuplicatedObjectIds);
    }
}
