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

use DateTime;
use District5\Date\Date;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\MondocEncryption\Adapters\Sodium\AES256Adapter;
use District5\MondocEncryption\Adapters\NullAdapter;
use District5\Mondoc\MondocConfig;
use District5\MondocEncryption\Exception\MondocEncryptionException;
use District5Tests\MondocTests\TestObjects\Model\MixedEncryptedModel;
use District5Tests\MondocTests\TestObjects\Model\MixedEncryptedWithEncryptedSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\PersonalDataWithoutEncryptionModel;
use District5Tests\MondocTests\TestObjects\Service\MixedEncryptedService;
use District5Tests\MondocTests\TestObjects\Service\MixedEncryptedWithEncryptedSubModelService;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;

/**
 * Class ModelFunctionalityTest.
 *
 * @package District5Tests\MondocTests
 *
 * @internal
 */
class ModelEncryptionTest extends MondocBaseTestAbstract
{
    /**
     * Switch between null and aes-256 adapters
     */
    public function testSwitchingAdapters()
    {
        MondocConfig::getInstance()->removeEncryptionAdapter();

        MondocConfig::getInstance()->setEncryptionAdapter(
            new NullAdapter()
        );
        $this->assertinstanceOf(NullAdapter::class, MondocConfig::getInstance()->getEncryptionAdapter());

        MondocConfig::getInstance()->removeEncryptionAdapter();
        $this->assertNull(MondocConfig::getInstance()->getEncryptionAdapter()); // defaults to null adapter

        MondocConfig::getInstance()->setEncryptionAdapter(
            new AES256Adapter(
                AES256Adapter::generateKey()
            )
        );
        $this->assertinstanceOf(AES256Adapter::class, MondocConfig::getInstance()->getEncryptionAdapter());

        MondocConfig::getInstance()->removeEncryptionAdapter();
        $this->assertNull(MondocConfig::getInstance()->getEncryptionAdapter()); // defaults to null adapter
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @throws MondocServiceMapErrorException
     */
    public function testCreateModel()
    {
        MondocConfig::getInstance()->setEncryptionAdapter(
            new AES256Adapter(
                '12345678901234567890123456789012'
            )
        );

        $date = Date::createYMDHISM(2001, 1, 1, 1, 1, 1);
        $model = new MixedEncryptedModel();
        $model->setFirstName('Joe');
        $model->setLastName('Bloggs');
        $model->setDateOfBirth($date);
        $model->setSocialSecurityNumber('123-45-6789');
        $sub = $model->getSubModel();
        $sub->setReligion('Jedi');
        $sub->setFavouriteColour('Blue');
        $this->assertTrue($model->save());

        $this->assertNotInstanceOf(DateTime::class, $model->asArray(true)['dateOfBirth']);
        $this->assertNotInstanceOf(UTCDateTime::class, $model->asArray(true)['dateOfBirth']);
        $this->assertIsString($model->asArray(true)['dateOfBirth']);

        $this->assertInstanceOf(UTCDateTime::class, $model->asArray()['dateOfBirth']);
        $this->assertNotEquals('123-45-6789', $model->asArray(true)['socialSecurityNumber']);
        $this->assertEquals('123-45-6789', $model->asArray()['socialSecurityNumber']);

        $this->assertTrue($model->hasObjectId());

        $retrievedModel = MixedEncryptedService::getById($model->getObjectId());
        $this->assertInstanceOf(MixedEncryptedModel::class, $retrievedModel);
        $this->assertEquals($model->getObjectIdString(), $retrievedModel->getObjectIdString());
        $this->assertEquals('Joe', $retrievedModel->getFirstName());
        $this->assertEquals('Bloggs', $retrievedModel->getLastName());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $retrievedModel->getDateOfBirth()->format('Y-m-d H:i:s'));
        $this->assertEquals('123-45-6789', $retrievedModel->getSocialSecurityNumber());
        $this->assertEquals('Jedi', $retrievedModel->getSubModel()->getReligion());
        $this->assertEquals('Blue', $retrievedModel->getSubModel()->getFavouriteColour());

        $this->assertTrue($retrievedModel->delete());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @throws MondocServiceMapErrorException
     */
    public function testCreateModelWithNullAdapterHasNoImpact()
    {
        MondocConfig::getInstance()->setEncryptionAdapter(
            new NullAdapter()
        );

        $date = Date::createYMDHISM(2001, 1, 1, 1, 1, 1);
        $model = new MixedEncryptedModel();
        $model->setFirstName('Joe');
        $model->setLastName('Bloggs');
        $model->setDateOfBirth($date);
        $model->setSocialSecurityNumber('123-45-6789');
        $sub = $model->getSubModel();
        $sub->setReligion('Jedi');
        $sub->setFavouriteColour('Blue');
        $this->assertTrue($model->save());

        $this->assertInstanceOf(UTCDateTime::class, $model->asArray(true)['dateOfBirth']);
        $this->assertInstanceOf(UTCDateTime::class, $model->asArray()['dateOfBirth']);

        $this->assertInstanceOf(UTCDateTime::class, $model->asArray()['dateOfBirth']);
        $this->assertEquals('123-45-6789', $model->asArray(true)['socialSecurityNumber']);
        $this->assertEquals('123-45-6789', $model->asArray()['socialSecurityNumber']);

        $this->assertTrue($model->hasObjectId());

        $retrievedModel = MixedEncryptedService::getById($model->getObjectId());
        $this->assertInstanceOf(MixedEncryptedModel::class, $retrievedModel);
        $this->assertEquals($model->getObjectIdString(), $retrievedModel->getObjectIdString());
        $this->assertEquals('Joe', $retrievedModel->getFirstName());
        $this->assertEquals('Bloggs', $retrievedModel->getLastName());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $retrievedModel->getDateOfBirth()->format('Y-m-d H:i:s'));
        $this->assertEquals('123-45-6789', $retrievedModel->getSocialSecurityNumber());
        $this->assertEquals('Jedi', $retrievedModel->getSubModel()->getReligion());
        $this->assertEquals('Blue', $retrievedModel->getSubModel()->getFavouriteColour());

        $this->assertTrue($retrievedModel->delete());
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function testAdapterWithInvalidKeyThrows()
    {
        MondocConfig::getInstance()->setEncryptionAdapter(
            new AES256Adapter(
                AES256Adapter::generateKey()
            )
        );

        $date = Date::createYMDHISM(2001, 1, 1, 1, 1, 1);

        $model = new MixedEncryptedModel();
        $model->setFirstName('Joe');
        $model->setLastName('Bloggs');
        $model->setDateOfBirth($date);

        $encrypted = $model->asArray(true);
        $encrypted['subModel'] = new BSONDocument(
            $encrypted['subModel']
        );
        $encrypted = new BSONDocument($encrypted);

        MondocConfig::getInstance()->setEncryptionAdapter(
            new AES256Adapter(
                AES256Adapter::generateKey()
            )
        );
        // invalid key now
        $this->expectException(MondocEncryptionException::class);
        MixedEncryptedModel::inflateSingleBsonDocument($encrypted);

        $this->fail('Should not have inflated');
    }

    /**
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocEncryptionException
     * @throws MondocException
     */
    public function testCreateModelWithEncryptedSubmodel()
    {
        MondocConfig::getInstance()->setEncryptionAdapter(
            new AES256Adapter(
                AES256Adapter::generateKey()
            )
        );

        $date = Date::createYMDHISM(2001, 1, 1, 1, 1, 1);
        $model = new MixedEncryptedWithEncryptedSubModel();
        $model->setFirstName('Joe');
        $model->setLastName('Bloggs');
        $model->setDateOfBirth($date);
        $model->setSocialSecurityNumber('123-45-6789');
        $sub = $model->getSubModel();
        $sub->setReligion('Jedi');
        $sub->setFavouriteColour('Blue');
        $this->assertTrue($model->save());

        $this->assertNotInstanceOf(DateTime::class, $model->asArray(true)['dateOfBirth']);
        $this->assertNotInstanceOf(UTCDateTime::class, $model->asArray(true)['dateOfBirth']);
        $this->assertNotInstanceOf(PersonalDataWithoutEncryptionModel::class, $model->asArray(true)['subModel']);
        $this->assertIsString($model->asArray(true)['dateOfBirth']);
        $this->assertIsString($model->asArray(true)['subModel']);

        $this->assertInstanceOf(UTCDateTime::class, $model->asArray()['dateOfBirth']);
        $this->assertNotEquals('123-45-6789', $model->asArray(true)['socialSecurityNumber']);
        $this->assertEquals('123-45-6789', $model->asArray()['socialSecurityNumber']);

        $this->assertTrue($model->hasObjectId());

        $retrievedModel = MixedEncryptedWithEncryptedSubModelService::getById($model->getObjectId());
        $this->assertInstanceOf(MixedEncryptedWithEncryptedSubModel::class, $retrievedModel);
        $this->assertIsString($retrievedModel->getOriginalBsonDocument()->dateOfBirth);
        $this->assertIsString($retrievedModel->getOriginalBsonDocument()->socialSecurityNumber);
        $this->assertIsString($retrievedModel->getOriginalBsonDocument()->subModel);

        $this->assertEquals($model->getObjectIdString(), $retrievedModel->getObjectIdString());

        $this->assertEquals('Joe', $retrievedModel->getFirstName());
        $this->assertEquals('Bloggs', $retrievedModel->getLastName());

        $this->assertEquals($date->format('Y-m-d H:i:s'), $retrievedModel->getDateOfBirth()->format('Y-m-d H:i:s'));

        $this->assertEquals('123-45-6789', $retrievedModel->getSocialSecurityNumber());

        $this->assertInstanceOf(PersonalDataWithoutEncryptionModel::class, $retrievedModel->getSubModel());
        $this->assertEquals('Jedi', $retrievedModel->getSubModel()->getReligion());
        $this->assertEquals('Blue', $retrievedModel->getSubModel()->getFavouriteColour());

        $this->assertTrue($retrievedModel->delete());
    }

    /**
     * @return void
     * @throws MondocConfigConfigurationException
     */
    protected function tearDown(): void
    {
        MixedEncryptedService::deleteMulti([]);
        MixedEncryptedWithEncryptedSubModelService::deleteMulti([]);
    }
}
