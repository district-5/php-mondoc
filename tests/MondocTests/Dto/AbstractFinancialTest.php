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
use District5Tests\MondocTests\MondocBaseTest;

/**
 * Class AggregateFinancialCandleDtoTest.
 *
 * @package District5Tests\MondocTests\Dto
 *
 * @internal
 */
abstract class AbstractFinancialTest extends MondocBaseTest
{
    protected function getTestData(): array
    {
        $now = Date::createYMDHISM(2024, 6, 2, 12, 0, 0); // 2024-06-02 12:00:00 (UTC)
        return [
            [
                'price' => 2.5,
                'date' => $now,
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.01,
                'date' => Date::modify($now, true)->minus()->minutes(1),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.3,
                'date' => Date::modify($now, true)->minus()->minutes(2),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.2,
                'date' => Date::modify($now, true)->minus()->minutes(3),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.1,
                'date' => Date::modify($now, true)->minus()->minutes(4),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.3,
                'date' => Date::modify($now, true)->minus()->minutes(5),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.4,
                'date' => Date::modify($now, true)->minus()->minutes(6),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.5,
                'date' => Date::modify($now, true)->minus()->minutes(7),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.6,
                'date' => Date::modify($now, true)->minus()->minutes(8),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.7,
                'date' => Date::modify($now, true)->minus()->minutes(9),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.8,
                'date' => Date::modify($now, true)->minus()->minutes(10),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.9,
                'date' => Date::modify($now, true)->minus()->minutes(11),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.8,
                'date' => Date::modify($now, true)->minus()->minutes(12),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.7,
                'date' => Date::modify($now, true)->minus()->minutes(13),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.6,
                'date' => Date::modify($now, true)->minus()->minutes(14),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.5,
                'date' => Date::modify($now, true)->minus()->minutes(15),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.4,
                'date' => Date::modify($now, true)->minus()->minutes(16),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.3,
                'date' => Date::modify($now, true)->minus()->minutes(17),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.2,
                'date' => Date::modify($now, true)->minus()->minutes(18),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.1,
                'date' => Date::modify($now, true)->minus()->minutes(19),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 2.0,
                'date' => Date::modify($now, true)->minus()->minutes(20),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.9,
                'date' => Date::modify($now, true)->minus()->minutes(21),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.8,
                'date' => Date::modify($now, true)->minus()->minutes(22),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.7,
                'date' => Date::modify($now, true)->minus()->minutes(23),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.6,
                'date' => Date::modify($now, true)->minus()->minutes(24),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.5,
                'date' => Date::modify($now, true)->minus()->minutes(25),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.4,
                'date' => Date::modify($now, true)->minus()->minutes(26),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.3,
                'date' => Date::modify($now, true)->minus()->minutes(27),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.2,
                'date' => Date::modify($now, true)->minus()->minutes(28),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.1,
                'date' => Date::modify($now, true)->minus()->minutes(29),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 1.0,
                'date' => Date::modify($now, true)->minus()->minutes(30),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 0.9,
                'date' => Date::modify($now, true)->minus()->minutes(31),
                'symbol' => 'JOES-COIN'
            ],
            [
                'price' => 0.8,
                'date' => Date::modify($now, true)->minus()->minutes(32),
                'symbol' => 'JOES-COIN'
            ],
        ];
    }
}
