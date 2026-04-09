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

namespace District5Tests\MondocTests;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\TestObjects\Model\HookedModel;
use District5Tests\MondocTests\TestObjects\Service\HookedService;

/**
 * Class LifecycleHooksTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class LifecycleHooksTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testInsertHooksFire(): void
    {
        $m = new HookedModel();
        $m->setName('Alice')->setAge(30);

        $this->assertEquals(0, $m->getBeforeInsertCount());
        $this->assertEquals(0, $m->getAfterInsertCount());

        $this->assertTrue($m->save());

        $this->assertEquals(1, $m->getBeforeInsertCount());
        $this->assertEquals(1, $m->getAfterInsertCount());
        $this->assertTrue($m->hasObjectId());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testBeforeInsertVetoPreventsInsert(): void
    {
        $m = new HookedModel();
        $m->setName('Bob')->setAge(25);
        $m->setVetoInsert(true);

        $this->assertFalse($m->save());
        $this->assertEquals(1, $m->getBeforeInsertCount());
        $this->assertEquals(0, $m->getAfterInsertCount());
        $this->assertFalse($m->hasObjectId());
        $this->assertEquals(0, HookedService::countAll());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testUpdateHooksFire(): void
    {
        $m = new HookedModel();
        $m->setName('Charlie')->setAge(20);
        $this->assertTrue($m->save());

        $m->setAge(21);

        $this->assertEquals(0, $m->getBeforeUpdateCount());
        $this->assertEquals(0, $m->getAfterUpdateCount());

        $this->assertTrue($m->save());

        $this->assertEquals(1, $m->getBeforeUpdateCount());
        $this->assertEquals(1, $m->getAfterUpdateCount());

        $retrieved = HookedService::getById($m->getObjectId());
        $this->assertEquals(21, $retrieved->getAge());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testBeforeUpdateVetoPreventsUpdate(): void
    {
        $m = new HookedModel();
        $m->setName('Dave')->setAge(40);
        $this->assertTrue($m->save());

        $m->setAge(99);
        $m->setVetoUpdate(true);

        $this->assertFalse($m->save());
        $this->assertEquals(1, $m->getBeforeUpdateCount());
        $this->assertEquals(0, $m->getAfterUpdateCount());

        $retrieved = HookedService::getById($m->getObjectId());
        $this->assertEquals(40, $retrieved->getAge());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testDeleteHooksFire(): void
    {
        $m = new HookedModel();
        $m->setName('Eve')->setAge(28);
        $this->assertTrue($m->save());

        $id = $m->getObjectIdString();

        $this->assertEquals(0, $m->getBeforeDeleteCount());
        $this->assertEquals(0, $m->getAfterDeleteCount());

        $this->assertTrue($m->delete());

        $this->assertEquals(1, $m->getBeforeDeleteCount());
        $this->assertEquals(1, $m->getAfterDeleteCount());
        $this->assertFalse($m->hasObjectId());
        $this->assertNull(HookedService::getById($id));
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testBeforeDeleteVetoPreventsDelete(): void
    {
        $m = new HookedModel();
        $m->setName('Frank')->setAge(35);
        $this->assertTrue($m->save());

        $m->setVetoDelete(true);

        $this->assertFalse($m->delete());
        $this->assertEquals(1, $m->getBeforeDeleteCount());
        $this->assertEquals(0, $m->getAfterDeleteCount());
        $this->assertTrue($m->hasObjectId());
        $this->assertEquals(1, HookedService::countAll());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testInsertMultiHooksFire(): void
    {
        $a = new HookedModel();
        $a->setName('Anna')->setAge(22);

        $b = new HookedModel();
        $b->setName('Ben')->setAge(19);

        $this->assertTrue(HookedService::insertMulti([$a, $b]));

        $this->assertEquals(1, $a->getBeforeInsertCount());
        $this->assertEquals(1, $a->getAfterInsertCount());
        $this->assertEquals(1, $b->getBeforeInsertCount());
        $this->assertEquals(1, $b->getAfterInsertCount());
        $this->assertTrue($a->hasObjectId());
        $this->assertTrue($b->hasObjectId());
        $this->assertEquals(2, HookedService::countAll());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testInsertMultiVetoSkipsVetoedModel(): void
    {
        $a = new HookedModel();
        $a->setName('Carol')->setAge(30);

        $b = new HookedModel();
        $b->setName('Dan')->setAge(25);
        $b->setVetoInsert(true);

        $this->assertTrue(HookedService::insertMulti([$a, $b]));

        $this->assertEquals(1, $a->getBeforeInsertCount());
        $this->assertEquals(1, $a->getAfterInsertCount());
        $this->assertEquals(1, $b->getBeforeInsertCount());
        $this->assertEquals(0, $b->getAfterInsertCount());
        $this->assertTrue($a->hasObjectId());
        $this->assertFalse($b->hasObjectId());
        $this->assertEquals(1, HookedService::countAll());
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public function testDefaultHooksReturnTrueAndDoNothing(): void
    {
        // Test that base MondocAbstractModel hook defaults work (no-op)
        $m = new HookedModel();
        $m->setVetoInsert(false);
        $m->setVetoUpdate(false);
        $m->setVetoDelete(false);

        $this->assertTrue($m->beforeInsert());
        $this->assertTrue($m->beforeUpdate());
        $this->assertTrue($m->beforeDelete());
        // afterInsert/Update/Delete return void — just confirm they don't throw
        $m->afterInsert();
        $m->afterUpdate();
        $m->afterDelete();
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function setUp(): void
    {
        HookedService::deleteMulti([]);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        HookedService::deleteMulti([]);
    }
}
