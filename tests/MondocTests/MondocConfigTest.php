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
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\MondocConfig;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use MongoDB\Database;

/**
 * Class MondocConfigTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class MondocConfigTest extends MondocBaseTestAbstract
{
    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testGetDatabaseForConnection()
    {
        $this->assertIsString(MondocConfig::getInstance()->getDatabaseName());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testGetDatabaseForInvalidConnection()
    {
        $this->expectException(MondocConfigConfigurationException::class);
        MondocConfig::getInstance()->getDatabaseName('invalid-connection');
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testAddDatabaseForAlternateKey()
    {
        $this->expectNotToPerformAssertions();
        MondocConfig::getInstance()->addDatabase(
            MondocConfig::getInstance()->getDatabase(),
            'alternate'
        );
        MondocConfig::getInstance()->getDatabase('alternate');
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testGetDatabase()
    {
        $database = MondocConfig::getInstance()->getDatabase();
        $this->assertEquals($this->getDatabaseName(), $database->getDatabaseName());

        $service = MyService::getMongo();
        $this->assertEquals($this->getDatabaseName(), $service->getDatabaseName());
    }

    /**
     * @throws MondocServiceMapErrorException
     */
    public function testValidServiceMapRetrieval()
    {
        $map = MondocConfig::getInstance()->getServiceMap();
        $this->assertIsArray($map);
        $this->assertArrayHasKey(MyModel::class, $map);
        $this->assertArrayHasKey(DateModel::class, $map);
        $this->assertEquals(MyService::class, $map[MyModel::class]);
        $this->assertEquals(DateService::class, $map[DateModel::class]);

        $this->assertEquals(MyModel::class, MondocConfig::getInstance()->getModelForService(MyService::class));
        $this->assertEquals(DateModel::class, MondocConfig::getInstance()->getModelForService(DateService::class));

        $this->assertEquals(MyService::class, MondocConfig::getInstance()->getServiceForModel(MyModel::class));
        $this->assertEquals(DateService::class, MondocConfig::getInstance()->getServiceForModel(DateModel::class));
    }

    public function testInvalidServiceMapRetrievalThrowsWhenRequestingService()
    {
        $this->expectException(MondocServiceMapErrorException::class);
        $this->assertNull(MondocConfig::getInstance()->getModelForService('NonExistentService'));
    }

    public function testInvalidServiceMapRetrievalThrowsWhenRequestingModel()
    {
        $this->expectException(MondocServiceMapErrorException::class);
        $this->assertNull(MondocConfig::getInstance()->getServiceForModel('NonExistentModel'));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testInvalidGetsFromConfigWhenRequestingDatabase()
    {
        $this->expectException(MondocConfigConfigurationException::class);
        $this->assertNull(MondocConfig::getInstance()->getDatabase('non-existent'));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testInvalidGetsFromConfigWhenRequestingCollection()
    {
        $this->expectException(MondocConfigConfigurationException::class);
        $this->assertNull(MondocConfig::getInstance()->getCollection('blah-blah', 'non-existent'));
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function testReconstruct()
    {
        $old = MondocConfig::getInstance();
        $oldDb = $old->getDatabase();
        $oldMap = $old->getServiceMap();
        $newInstance = MondocConfig::getInstance()->reconstruct();
        try {
            $newInstance->getDatabase();
            $this->fail('Expected exception not thrown');
        } catch (MondocConfigConfigurationException $e) {
            $this->assertEquals('MondocConfig: database connection not found for connection ID: default', $e->getMessage());
        }
        $newInstance->addDatabase($oldDb);
        $newInstance->addDatabase($oldDb, 'mondoc_retention');
        $newInstance->setServiceMap($oldMap);
        $this->assertInstanceOf(Database::class, $newInstance->getDatabase());
        $this->assertEquals($old->getServiceMap(), $newInstance->getServiceMap());
    }
}
