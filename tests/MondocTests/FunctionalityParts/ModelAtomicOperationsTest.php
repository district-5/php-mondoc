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
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class ModelAtomicOperationsTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 *
 * @internal
 */
class ModelAtomicOperationsTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function setUp(): void
    {
        MyService::deleteMulti([]);
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MyService::deleteMulti([]);
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testIncAndDecAffectAggregates()
    {
        $this->assertEquals(0, MyService::countAll());

        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe ' . $this->getUniqueKey());
        $this->assertTrue($m->save());

        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::inc($m->getObjectId(), 'age', 2));
        $this->assertEquals(4, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::dec($m->getObjectId(), 'age', 2));
        $this->assertEquals(2, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getAverage('age', ['name' => $m->getName()]));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testUpdateOneAffectsAggregates()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe ' . $this->getUniqueKey());
        $this->assertTrue($m->save());

        $this->assertTrue(MyService::updateOne(['_id' => $m->getObjectId()], ['$set' => ['age' => 4]]));
        $this->assertEquals(4, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(4, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(4, MyService::aggregate()->getSum('age'));

        $this->assertTrue(MyService::updateOne(['_id' => $m->getObjectId()], ['$set' => ['age' => 2]]));
        $this->assertEquals(2, MyService::getById($m->getObjectId())->getAge());
        $this->assertEquals(2, MyService::aggregate()->getAverage('age'));
        $this->assertEquals(2, MyService::aggregate()->getSum('age'));
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     */
    public function testUpdateManyByCriteriaAndQueryBuilder()
    {
        $m = new MyModel();
        $m->setAge(2);
        $m->setName('Joe ' . $this->getUniqueKey());
        $this->assertTrue($m->save());

        $this->assertTrue(MyService::updateMany(['age' => 2], ['$set' => ['age' => 123]]));
        $this->assertEquals(123, MyService::getById($m->getObjectId())->getAge());

        $builderMany = MyService::getQueryBuilder();
        $builderMany->addQueryPart(ValueEqualTo::get()->integer('age', 123));
        $this->assertTrue(MyService::updateManyByQueryBuilder($builderMany, ['$set' => ['age' => 456]]));
        $this->assertEquals(456, MyService::getById($m->getObjectId())->getAge());

        $builderOne = MyService::getQueryBuilder();
        $builderOne->addQueryPart(ValueEqualTo::get()->integer('age', 456));
        $this->assertTrue(MyService::updateOneByQueryBuilder($builderOne, ['$set' => ['age' => 789]]));
        $this->assertEquals(789, MyService::getById($m->getObjectId())->getAge());
    }
}
