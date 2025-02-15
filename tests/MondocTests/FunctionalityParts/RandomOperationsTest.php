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
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\BSON\ObjectId;

/**
 * Class RandomOperationsTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class RandomOperationsTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testRandomDatabaseOperations()
    {
        MyService::deleteMulti([]);

        $m = new MyModel();
        $this->assertTrue($m->setName('Joe')->setAge(2)->save());
        $m2 = new MyModel();
        $this->assertTrue($m2->setName('Jane')->setAge(4)->save());

        $this->assertEquals(2, MyService::countAll([]));
        $values = MyService::getDistinctValuesForKey('name');
        $this->assertCount(2, $values);
        $this->assertContains('Joe', $values);
        $this->assertContains('Jane', $values);

        $this->assertEquals(1, MyService::countWhereKeyEqualsValue('name', 'Joe'));
        $this->assertEquals(2, MyService::estimateDocumentCount());

        $delete = MyService::deleteMulti([
            '_id' => [
                '$in' => [
                    $m->getObjectId(),
                    $m2->getObjectId()
                ]
            ]
        ]);
        $this->assertEquals(2, $delete);

        $mInvalid = new MyModel();
        $mInvalid->setObjectId(new ObjectId());
        $this->assertFalse(MyService::deleteModel($mInvalid));
    }
}
