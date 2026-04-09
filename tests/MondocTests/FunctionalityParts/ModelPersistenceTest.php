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

use DateTime;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class ModelPersistenceTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class ModelPersistenceTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function setUp(): void
    {
        MyService::deleteMulti([]);
        DateService::deleteMulti([]);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::deleteMulti([]);
        DateService::deleteMulti([]);
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testInsertionAndBSONArePresent()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');

        $this->assertCount(2, $m->getDirty());
        $this->assertNull($m->getOriginalBsonDocument());

        $this->assertTrue($m->save());

        $this->assertEmpty($m->getDirty());
        $this->assertNotNull($m->getOriginalBsonDocument());
        $this->assertEquals(2, $m->getOriginalBsonDocument()->getArrayCopy()['age']);
        $this->assertEquals('Joe', $m->getOriginalBsonDocument()->getArrayCopy()['name']);
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testDeleteWorksAsExpected()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe');
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());

        $this->assertTrue($m->save()); // save again (update) while it exists
        $this->assertTrue($m->delete());
        $this->assertFalse($m->hasObjectId());

        $this->assertFalse($m->delete()); // already deleted
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testModelHasAndDoesNotHaveObjectId()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName($this->getUniqueKey());

        $this->assertFalse($m->hasObjectId());
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public function testDateFieldRoundTrip()
    {
        $nowDate = new DateTime();
        $m = new DateModel();
        $m->setDate($nowDate);

        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(false)->format('Y-m-d H:i:s'));
        $this->assertEquals($nowDate->format('Y-m-d H:i:s'), $m->getDate(true)->toDateTime()->format('Y-m-d H:i:s'));

        $this->assertFalse($m->hasObjectId());
        $this->assertTrue($m->save());
        $this->assertTrue($m->hasObjectId());

        $found = DateService::getById($m->getObjectId());
        /* @var $found DateModel */
        $this->assertEquals($m->getObjectIdString(), $found->getObjectIdString());
        $this->assertEquals($m->getDate(false)->format('Y-m-d H:i:s'), $found->getDate(false)->format('Y-m-d H:i:s'));
    }
}
