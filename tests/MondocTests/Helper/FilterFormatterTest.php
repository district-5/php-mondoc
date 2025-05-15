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

namespace District5Tests\MondocTests\Helper;

use DateTime;
use District5\Date\Date;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\FilterFormatter;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;

/**
 * Class FilterFormatterTest.
 *
 * @package District5Tests\MondocTests\Helper
 */
class FilterFormatterTest extends MondocBaseTestAbstract
{
    public function testFilterFormatter()
    {
        $raw = [
            'foo' => 'bar',
            'baz' => 'qux',
            'array' => [
                'one' => 'two',
                'three' => 'four'
            ],
            'date' => new DateTime('2016-01-01 00:00:00')
        ];
        $formatted = FilterFormatter::format($raw);
        $this->assertArrayHasKey('foo', $formatted);
        $this->assertArrayHasKey('baz', $formatted);
        $this->assertArrayHasKey('array', $formatted);
        $this->assertArrayHasKey('date', $formatted);

        $this->assertEquals('bar', $formatted['foo']);
        $this->assertEquals('qux', $formatted['baz']);
        $this->assertIsArray($formatted['array']);
        $this->assertArrayHasKey('one', $formatted['array']);
        $this->assertArrayHasKey('three', $formatted['array']);
        $this->assertEquals('two', $formatted['array']['one']);
        $this->assertEquals('four', $formatted['array']['three']);

        $this->assertInstanceOf(UTCDateTime::class, $formatted['date']);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function testThatUpdatingDateUsingDateTimeResultsInUTCDateTime(): void
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-01 00:00:00');
        $newDate = DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-01 00:00:01');

        $original = new DateModel();
        $original->setDate($date);
        $this->assertTrue($original->save());
        $this->assertTrue($original->hasObjectId());

        $this->assertTrue(DateService::updateOne(['_id' => $original->getObjectId()], ['$set' => ['date' => $newDate]]));

        $rawCollection = DateService::getCollection(DateService::class);
        $document = $rawCollection->findOne(['_id' => $original->getObjectId()]);
        /* @var $document BSONDocument */
        $arrayCopy = $document->getArrayCopy();
        $this->assertArrayHasKey('date', $arrayCopy);
        $this->assertInstanceOf(UTCDateTime::class, $document['date']);
        $this->assertEquals(Date::output($newDate)->toTimestamp(true), Date::output($document['date']->toDateTime())->toTimestamp(true));

        $this->assertTrue($original->delete());
    }
}
