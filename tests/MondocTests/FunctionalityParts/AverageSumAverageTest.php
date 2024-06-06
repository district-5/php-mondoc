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

namespace District5Tests\MondocTests\FunctionalityParts;

use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class AverageSumAverageTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class AverageSumAverageTest extends MondocBaseTest
{
    public function testAverageSumAverageWorkCorrectly()
    {
        MyService::deleteMulti([]);
        $this->assertEquals(0, MyService::aggregate()->getSum('age'));
        $this->assertEquals(0, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(0, MyService::countAll([]));

        $ages = [2 => 'Joe', 4 => 'Joe', 6 => 'Jane'];
        foreach ($ages as $age => $name) {
            $m = new MyModel();
            $m->setAge($age);
            $m->setName($name);
            $this->assertTrue($m->save());
        }

        $this->runAverage();
        $this->runSum();
    }

    private function runAverage(): void
    {
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(3, MyService::aggregate()->getAverage('age', ['name' => 'Joe']));
        $this->assertEquals(3, MyService::aggregate()->getAverage('age', ['name' => ['$eq' => 'Joe']]));
    }

    private function runSum(): void
    {
        $this->assertEquals(12, MyService::aggregate()->getSum('age'));
        $this->assertEquals(6, MyService::aggregate()->getSum('age', ['name' => 'Joe']));
        $this->assertEquals(6, MyService::aggregate()->getSum('age', ['name' => ['$eq' => 'Joe']]));
    }
}
