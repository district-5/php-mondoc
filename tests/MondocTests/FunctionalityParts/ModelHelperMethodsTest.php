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

use DateTime;
use District5Tests\MondocTests\MondocBaseTest;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;
use stdClass;

/**
 * Class ModelHelperMethodsTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class ModelHelperMethodsTest extends MondocBaseTest
{
    public function testCommonSubMethods()
    {
        $m = new MyModel();

        $obj = new stdClass();
        $obj->a = 'b';
        $obj->obj = new stdClass();
        $obj->obj->c = 'd';
        $obj->array = ['a', 'b'];
        $array = ['foo' => 'bar', 'baz' => 'qux', 'array' => ['a', 'b'], 'obj' => $obj];
        $converted = $m->proxyArrayToArray($array);
        $this->assertIsArray($converted);
        $this->assertArrayHasKey('foo', $converted);
        $this->assertArrayHasKey('baz', $converted);
        $this->assertArrayHasKey('obj', $converted);
        $this->assertArrayHasKey('array', $converted);
        $this->assertIsArray($converted['array']);
        $this->assertArrayHasKey('0', $converted['array']);
        $this->assertArrayHasKey('1', $converted['array']);
        $this->assertIsArray($converted['obj']);
        $this->assertArrayHasKey('a', $converted['obj']);

        $convertedObj = $m->proxyObjectToArray($obj);
        $this->assertIsArray($convertedObj);
        $this->assertArrayHasKey('a', $convertedObj);
        $this->assertArrayHasKey('obj', $convertedObj);
        $this->assertArrayHasKey('array', $convertedObj);
        $this->assertIsArray($convertedObj['array']);
        $this->assertArrayHasKey('0', $convertedObj['array']);
        $this->assertArrayHasKey('1', $convertedObj['array']);
        $this->assertIsArray($convertedObj['obj']);
        $this->assertArrayHasKey('c', $convertedObj['obj']);
    }
}
