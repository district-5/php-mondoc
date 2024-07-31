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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class PercentileTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class PercentileTest extends MondocBaseTest
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testPercentile()
    {
        MyService::deleteMulti([]);

        $i = 0;
        $models = [];
        while ($i < 10) {
            $i++;
            $m = new MyModel();
            $m->setName(uniqid());
            $m->setAge($i);
            $models[] = $m;
        }
        MyService::insertMulti($models);
        foreach ($models as $model) {
            $this->assertTrue($model->hasObjectId());
        }

        $this->assertEquals(1, MyService::aggregate()->getPercentile('age', .0));
        $this->assertEquals(2, MyService::aggregate()->getPercentile('age', .1));
        $this->assertEquals(3, MyService::aggregate()->getPercentile('age', .2));
        $this->assertEquals(4, MyService::aggregate()->getPercentile('age', .3));
        $this->assertEquals(5, MyService::aggregate()->getPercentile('age', .4));
        $this->assertEquals(6, MyService::aggregate()->getPercentile('age', .5));
        $this->assertEquals(7, MyService::aggregate()->getPercentile('age', .6));
        $this->assertEquals(8, MyService::aggregate()->getPercentile('age', .7));
        $this->assertEquals(9, MyService::aggregate()->getPercentile('age', .8));
        $this->assertEquals(10, MyService::aggregate()->getPercentile('age', .9));
        $this->assertNull(MyService::aggregate()->getPercentile('age', 1));
        $this->assertNull(MyService::aggregate()->getPercentile('age', 2, 1, ['foo' => 'bar']));

        $this->assertEquals(10, MyService::aggregate()->getPercentile('age', .0, -1));
        $this->assertEquals(9, MyService::aggregate()->getPercentile('age', .1, -1));
        $this->assertEquals(8, MyService::aggregate()->getPercentile('age', .2, -1));
        $this->assertEquals(7, MyService::aggregate()->getPercentile('age', .3, -1));
        $this->assertEquals(6, MyService::aggregate()->getPercentile('age', .4, -1));
        $this->assertEquals(5, MyService::aggregate()->getPercentile('age', .5, -1));
        $this->assertEquals(4, MyService::aggregate()->getPercentile('age', .6, -1));
        $this->assertEquals(3, MyService::aggregate()->getPercentile('age', .7, -1));
        $this->assertEquals(2, MyService::aggregate()->getPercentile('age', .8, -1));
        $this->assertEquals(1, MyService::aggregate()->getPercentile('age', .9, -1));
    }
}
