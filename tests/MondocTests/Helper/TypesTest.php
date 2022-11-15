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
use District5\Mondoc\Helper\MondocTypes;
use District5Tests\MondocTests\MondocBaseTest;
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
}
