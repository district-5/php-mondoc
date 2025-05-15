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

namespace District5Tests\MondocTests\FunctionalityParts;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class MinMaxTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class MinMaxTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testMinNumber()
    {
        MyService::deleteMulti([]);

        $name = 'Zorro';
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($name);
        $this->assertTrue($m->save());

        $this->assertEquals(2, MyService::aggregate()->getMin('age'));
        $this->assertEquals(2, MyService::aggregate()->getMin('age', ['name' => 'Zorro']));
        $this->assertEquals(2, MyService::aggregate()->getMax('age'));
        $this->assertEquals(2, MyService::aggregate()->getMax('age', ['name' => 'Zorro']));
        $this->assertEquals('Zorro', MyService::aggregate()->getMin('name'));
        $this->assertEquals('Zorro', MyService::aggregate()->getMax('name'));

        $this->assertNull(MyService::aggregate()->getMin('age', ['name' => 'noSuchName']));
        $this->assertNull(MyService::aggregate()->getMax('age', ['name' => 'noSuchName']));

        $name = 'Adrian';
        $n = new MyModel();
        $n->setAge(1);
        $n->setName($name);
        $this->assertTrue($n->save());

        $this->assertEquals(1, MyService::aggregate()->getMin('age'));
        $this->assertEquals(2, MyService::aggregate()->getMax('age'));
        $this->assertEquals('Adrian', MyService::aggregate()->getMin('name'));
        $this->assertEquals('Zorro', MyService::aggregate()->getMax('name'));
    }
}
