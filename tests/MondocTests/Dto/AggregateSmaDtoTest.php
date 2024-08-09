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

use DateTime;
use District5\Date\Date;
use District5\Mondoc\Dto\AggregateSmaDto;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5Tests\MondocTests\TestObjects\Model\FinancialCandleModel;
use District5Tests\MondocTests\TestObjects\Service\FinancialCandleService;
use Exception;

/**
 * Class AggregateSmaDtoTest.
 *
 * @package District5Tests\MondocTests\Dto
 *
 * @internal
 */
class AggregateSmaDtoTest extends AbstractFinancialTest
{
    public function testConstruction()
    {
        $date = Date::nowUtc();
        $dto = new AggregateSmaDto(1.01, 2, $date);
        $this->assertEquals(1.01, $dto->getPrice());
        $this->assertEquals(2, $dto->getSma());
        $this->assertEquals($date, $dto->getDate());
    }

    /**
     * @throws Exception
     */
    public function testDatabaseForAggregateSma()
    {
        FinancialCandleService::deleteMulti([]);
        $randUpper = 1000;
        $randLower = 500;

        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'price' => floatval(number_format(rand($randLower, $randUpper)*.9, 2, '.', '')),
                'date' => Date::modify(Date::nowUtc())->minus()->minutes($i),
                'symbol' => 'JOES-COIN'
            ];
        }

        foreach ($data as $datum) {
            $m = new FinancialCandleModel();
            $m->setPrice($datum['price']);
            $m->setDate($datum['date']);
            $m->setSymbol($datum['symbol']);
            $this->assertTrue($m->save());
        }

        $this->assertEquals(count($data), FinancialCandleService::countAll());

        $startDate = $data[count($data) - 1]['date'];
        $endDate = $data[0]['date'];
        $startDate = Date::modify($startDate, true)->minus()->minutes(1);
        $endDate = Date::modify($endDate, true)->plus()->minutes(1);

        $sma = FinancialCandleService::aggregate()->getFinancialSma(
            'symbol',
            'JOES-COIN',
            $startDate,
            $endDate,
            'date',
            'price',
            10,
            5,
            1,
            [
                'symbol' => 'JOES-COIN' // redundant
            ]
        );

        $this->assertGreaterThan(5, $sma);

        $this->assertGreaterThan(0, $sma[0]->getSma());
        $this->assertGreaterThan(0, $sma[0]->getPrice());
        $this->assertInstanceOf(DateTime::class, $sma[0]->getDate());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        FinancialCandleService::deleteMulti([]);
    }
}
