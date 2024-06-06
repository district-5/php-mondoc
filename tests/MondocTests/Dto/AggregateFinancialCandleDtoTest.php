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

namespace District5Tests\MondocTests\Dto;

use District5\Date\Date;
use District5\Mondoc\Dto\AggregateFinancialCandleDto;
use District5Tests\MondocTests\TestObjects\Model\FinancialCandleModel;
use District5Tests\MondocTests\TestObjects\Service\FinancialCandleService;
use InvalidArgumentException;

/**
 * Class AggregateFinancialCandleDtoTest.
 *
 * @package District5Tests\MondocTests\Dto
 *
 * @internal
 */
class AggregateFinancialCandleDtoTest extends AbstractFinancialTest
{
    public function testConstruction()
    {
        $date = Date::nowUtc();
        $dto = new AggregateFinancialCandleDto(2.5, 1.01, 1.3, 2.2, $date);
        $this->assertEquals(2.5, $dto->getHigh());
        $this->assertEquals(1.01, $dto->getLow());
        $this->assertEquals(1.3, $dto->getOpen());
        $this->assertEquals(2.2, $dto->getClose());
        $this->assertEquals($date, $dto->getDate());
    }

    public function testDatabaseInteractionsForFinancialCandles()
    {
        FinancialCandleService::deleteMulti([]);
        $data = $this->getTestData();
        foreach ($data as $datum) {
            $m = new FinancialCandleModel();
            $m->setPrice($datum['price']);
            $m->setDate($datum['date']);
            $m->setSymbol($datum['symbol']);
            $this->assertTrue($m->save());
        }

        $this->assertEquals(count($data), FinancialCandleService::countAll());

        $candles = FinancialCandleService::aggregate()->getFinancialXMinuteCandles(
            [
                'symbol' => 'JOES-COIN'
            ],
            [
                'symbol' => '$symbol'
            ],
            'price',
            'date',
            2,
            1,
            100
        );

        $this->assertCount(17, $candles);
        $this->assertEquals(2.5, $candles[0]->getHigh());
        $this->assertEquals(2.5, $candles[0]->getLow());
        $this->assertEquals(2.5, $candles[0]->getOpen());
        $this->assertEquals(2.5, $candles[0]->getClose());

        $this->assertEquals(1.3, $candles[1]->getHigh());
        $this->assertEquals(1.01, $candles[1]->getLow());
        $this->assertEquals(1.01, $candles[1]->getOpen());
        $this->assertEquals(1.3, $candles[1]->getClose());

        $this->assertEquals(2.8, $candles[5]->getHigh());
        $this->assertEquals(2.7, $candles[5]->getLow());
        $this->assertEquals(2.7, $candles[5]->getOpen());
        $this->assertEquals(2.8, $candles[5]->getClose());

        $reverseCandles = FinancialCandleService::aggregate()->getFinancialXMinuteCandles(
            [
                'symbol' => 'JOES-COIN'
            ],
            [
                'symbol' => '$symbol'
            ],
            'price',
            'date',
            2,
            -1,
            100
        );

        $this->assertCount(17, $reverseCandles);
        $this->assertEquals(0.9, $reverseCandles[0]->getHigh());
        $this->assertEquals(0.8, $reverseCandles[0]->getLow());
        $this->assertEquals(0.9, $reverseCandles[0]->getOpen());
        $this->assertEquals(0.8, $reverseCandles[0]->getClose());

        $this->assertEquals(1.1, $reverseCandles[1]->getHigh());
        $this->assertEquals(1.0, $reverseCandles[1]->getLow());
        $this->assertEquals(1.1, $reverseCandles[1]->getOpen());
        $this->assertEquals(1.0, $reverseCandles[1]->getClose());

        $this->assertEquals(1.3, $reverseCandles[2]->getHigh());
        $this->assertEquals(1.2, $reverseCandles[2]->getLow());
        $this->assertEquals(1.3, $reverseCandles[2]->getOpen());
        $this->assertEquals(1.2, $reverseCandles[2]->getClose());

        $this->assertEquals(1.9, $reverseCandles[5]->getHigh());
        $this->assertEquals(1.8, $reverseCandles[5]->getLow());
        $this->assertEquals(1.9, $reverseCandles[5]->getOpen());
        $this->assertEquals(1.8, $reverseCandles[5]->getClose());
    }

    public function testQueryingFinancialCandlesWithInvalidSortThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        FinancialCandleService::aggregate()->getFinancialXMinuteCandles(
            [],
            [
                'symbol' => '$symbol'
            ],
            'price',
            'date',
            2,
            0, // Invalid sort direction
            100
        );
    }
}
