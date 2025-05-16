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

use District5\Date\Date;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5Tests\MondocTests\MondocBaseTestAbstract;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;

/**
 * Class UpdateTest.
 *
 * @package District5Tests\MondocTests\FunctionalityParts
 */
class UpdateTest extends MondocBaseTestAbstract
{
    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testUpdateOneWithoutIdFails()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName('Jane');

        $this->assertFalse(MyService::update($m));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function testUpdateMultiWithoutIdFails()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName('Jane');

        $m2 = new MyModel();
        $m2->setAge(2);
        $m2->setName('Joe');

        $this->assertEmpty(MyService::updateManyModels([$m, $m2]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testUpdateOne()
    {
        $m = new MyModel();
        $m->setAge(1);
        $m->setName('Jane');
        $this->assertTrue($m->save());

        $m = MyService::getById($m->getObjectId());
        $m->setName('Joe');
        $this->assertTrue(MyService::update($m));

        $m = MyService::getById($m->getObjectId());
        $this->assertEquals('Joe', $m->getName());

        $this->assertTrue($m->delete());
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testUpdateMulti()
    {
        MyService::deleteMulti([]);

        $m = new MyModel();
        $m->setAge(1);
        $m->setName('Jane');

        $m2 = new MyModel();
        $m2->setAge(2);
        $m2->setName('Joe');

        $this->assertTrue(MyService::insertMulti([$m, $m2]));

        $models = MyService::getMultiByCriteria();
        $this->assertCount(2, $models);

        $models[0]->setName('Foo');
        $models[1]->setName('Bar');

        $this->assertEquals([true, true], MyService::updateManyModels($models));

        $this->assertEquals(2, MyService::deleteMulti([]));
    }

    /**
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testUpdateMultiWithDifferentServices()
    {
        MyService::deleteMulti([]);
        DateService::deleteMulti([]);

        $m = new MyModel();
        $m->setAge(1);
        $m->setName('Jane');

        $m2 = new MyModel();
        $m2->setAge(2);
        $m2->setName('Joe');

        $dt = new DateModel();
        $dt->setDate(Date::createYMDHISM(2010, 1, 1));

        $dt2 = new DateModel();
        $dt2->setDate(Date::createYMDHISM(2010, 1, 2));

        $this->assertTrue(MyService::insertMulti([$m, $m2]));

        $this->assertTrue(DateService::insertMulti([$dt, $dt2]));

        $models = MyService::getMultiByCriteria();
        $this->assertCount(2, $models);

        $dateModels = DateService::getMultiByCriteria();
        $this->assertCount(2, $dateModels);

        $models[0]->setName('Foo');
        $models[1]->setName('Bar');

        $dateModels[0]->setDate(Date::createYMDHISM(2011, 1, 1));
        $dateModels[1]->setDate(Date::createYMDHISM(2011, 1, 2));

        $this->assertEquals([true, true, true, true], MyService::updateManyModels(array_merge($models, $dateModels)));

        $this->assertEquals(2, MyService::deleteMulti([]));
        $this->assertEquals(2, DateService::deleteMulti([]));
    }
}
