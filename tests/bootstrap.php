<?php

use District5\Mondoc\MondocConfig;
use District5Tests\MondocTests\TestObjects\Model\AllTypesModel;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\FieldAliasTestModel;
use District5Tests\MondocTests\TestObjects\Model\FinancialCandleModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\MyModelWithSub;
use District5Tests\MondocTests\TestObjects\Model\SingleAndMultiNestedModel;
use District5Tests\MondocTests\TestObjects\Model\HelperTraitsModel;
use District5Tests\MondocTests\TestObjects\Model\HelperTraitsOtherModel;
use District5Tests\MondocTests\TestObjects\Service\AllTypesService;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\FieldAliasTestService;
use District5Tests\MondocTests\TestObjects\Service\FinancialCandleService;
use District5Tests\MondocTests\TestObjects\Service\HelperTraitsOtherService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use District5Tests\MondocTests\TestObjects\Service\MySubService;
use District5Tests\MondocTests\TestObjects\Service\SingleAndMultiNestedService;
use District5Tests\MondocTests\TestObjects\Service\HelperTraitsService;
use MongoDB\Client;

require __DIR__ . '/../vendor/autoload.php';

$connection = new Client(getenv('MONGO_CONNECTION_STRING'));
$mondoc = MondocConfig::getInstance();
/** @noinspection PhpRedundantOptionalArgumentInspection */
$mondoc->addDatabase(
    $connection->selectDatabase(getenv('MONGO_DATABASE') . php_uname('s')),
    'default'
);
$mondoc->setServiceMap([
    DateModel::class => DateService::class,
    MyModel::class => MyService::class,
    MyModelWithSub::class => MySubService::class,
    SingleAndMultiNestedModel::class => SingleAndMultiNestedService::class,
    FinancialCandleModel::class => FinancialCandleService::class,
    FieldAliasTestModel::class => FieldAliasTestService::class
]); // just to cover the setServiceMap method
$mondoc->addServiceMapping(
    HelperTraitsModel::class,
    HelperTraitsService::class
)->addServiceMapping(
    HelperTraitsOtherModel::class,
    HelperTraitsOtherService::class
)->addServiceMapping(
    AllTypesModel::class,
    AllTypesService::class
); // just to cover the addServiceMapping method

function cleanupCollections($mondoc): void
{
    $map = array_values($mondoc->getServiceMap());
    foreach ($map as $className) {
        $parts = explode('\\', $className);
        $collectionName = 'test_' . array_pop($parts);
        $mondoc->getDatabase()->dropCollection($collectionName);
    }
}

cleanupCollections($mondoc); // Start with a clean slate
$mondoc->getDatabase();