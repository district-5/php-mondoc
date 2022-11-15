District5 - Mondoc
======

### Composer...

Use composer to add this library as a dependency onto your project.

```
composer require district5/mondoc
```

Usage...
--------

#### Setting up connections...

The `MondoConnections` object is a singleton. Set this up somewhere in your code to initialise the connection, and
within your services you can define `protected static function getConnectionId(): string` to return the correct
identifier for the relevant model.

```php
<?php
use District5\Mondoc\MondocConfig;
use MongoDB\Client;

$connection = new Client('< mongo connection string >');
$database = $connection->selectDatabase('< database name >');

/** @noinspection PhpRedundantOptionalArgumentInspection */
$config = MondocConfig::getInstance();
$config->addDatabase(
    $database,
    'default' // a connection identifier ('default' is the default value).
);
// Add another one for something else...
// $config->addDatabase(
//     $database,
//     'authentication'
// );


$config->addServiceMapping(
    MyModel::class, // You can also just use a string like '\MyNamespace\Model\MyModel'
    MyService::class // You can also just use a string like '\MyNamespace\Service\MyService'
);
// Or you can use...
// $config->setServiceMap(
//     [
//         MyModel::class => MyService::class, // Also replaceable by strings
//         AnotherModel::class => AnotherService::class,
//     ]
// );

```

#### The data model

```php
<?php
namespace MyNs\Model;

use District5\Mondoc\Db\Model\MondocAbstractModel;use MyNs\Service\MyService;

/**
 * Class MyModel
 * @package MyNs\Model
 */
class MyModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $val
     * @return $this
     */
    public function setName(string $val)
    {
        $this->name = trim($val);
        $this->addDirty('name');
        return $this;
    }
}
```

#### The service layer

```php
<?php
namespace Myns\Service;

use MyNs\Model\MyModel;

/**
 * Class MyService
 * @package MyNs\Service
 */
class MyService extends AbstractService
{
    /**
     * @return string
     */
    protected static function getCollectionName(): string
    {
        return 'users';
    }
}
```

The logic for querying the database etc, is always performed in the service layer.

#### Nesting objects

You can nest objects in each other. The main model must extend `\District5\Mondoc\Db\Model\MondocAbstractModel` and have the sub
models defined in the `$mondocNested` array.

Sub models must extend `\District5\Mondoc\Db\Model\MondocAbstractSubModel`.

```php
use District5\Mondoc\Db\Model\MondocAbstractModel;use District5\Mondoc\Db\Model\MondocAbstractSubModel;

class FavouriteFood extends MondocAbstractSubModel
{
    protected $foodName;
    
    public function getFoodName()
    {
        return $this->foodName;
    }
}

class Car extends MondocAbstractSubModel
{
    protected $brand;
    protected $colour;
    
    public function getBrand()
    {
        return $this->brand;
    }
    
    public function getColour()
    {
        return $this->colour;
    }
}

class Person extends MondocAbstractModel
{
    protected $name = null;
    
    /**
     * @var FavouriteFood 
     */
    protected $favouriteFood = null;
    
    /**
     * @var Car 
     */
    protected $car = null;
    
    /**
     * @var string[] 
     */
    protected array $mondocNested = [
        'favouriteFood' => FavouriteFood::class,
        'car' => Car::class
    ];
    
    public function getFavouriteFoodName()
    {
        return $this->favouriteFood->getFoodName();
    }
    
    public function getCarBrand()
    {
        return $this->car->getBrand();
    }
    
    public function getCarColour()
    {
        return $this->car->getColour();
    }
}
```

Finding documents..


```php
<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

// count documents matching a filter
\District5Tests\MondocTests\Example\MyService::countAll([], []);
// count documents using a query builder
$builder = \District5Tests\MondocTests\Example\MyService::getQueryBuilder();
\District5Tests\MondocTests\Example\MyService::countAllByQueryBuilder($builder);

// get single model by id, accepts a string or ObjectId
\District5Tests\MondocTests\Example\MyService::getById('the-mongo-id');

// get multiple models by ids. accepts string or ObjectIds
\District5Tests\MondocTests\Example\MyService::getByIds(['an-id', 'another-id']);

// get single model with options
\District5Tests\MondocTests\Example\MyService::getOneByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// get multiple models with options
\District5Tests\MondocTests\Example\MyService::getMultiByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// paginating results by page number
$currentPage = 1;
$perPage = 10;
$sortByField = 'foo';
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\Example\MyService::getPaginationQueryHelper($currentPage, $perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\Example\MyService::getPage($pagination, $perPage, ['foo' => 'bar'], $sortByField, $sortDirection);

// paginating results by ID number descending (first page)
$currentId = null;
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\Example\MyService::getPaginationQueryHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\Example\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

// paginating results by ID number descending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\Example\MyService::getPaginationQueryHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\Example\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

// paginating results by ID number ascending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = 1;
$pagination = \District5Tests\MondocTests\Example\MyService::getPaginationQueryHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\Example\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

// get the distinct values for 'age' with a filter and options
\District5Tests\MondocTests\Example\MyService::getDistinctValuesForKey('age', ['foo' => 'bar'], ['sort' => ['age' => 1]]);

// average age with filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getAverage('age', ['foo' => 'bar']);

// 10% percentile, sorted asc with filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getPercentile('age', 0.1, 1, ['foo' => 'bar']);

// get sum of a field with a given filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getSum('age', ['foo' => 'bar']);
```


#### Useful information...

To use a pre-determined ObjectId as the document `_id`, you can call `setPresetMongoId` against the model. For example:

```php
<?php
$theId = new \MongoDB\BSON\ObjectId('61dfee5591efcf44e023d692');

$person = new Person();
$person->setPresetMongoId(new ObjectId());
$person->save();

echo $person->getMongoIdString(); // 61dfee5591efcf44e023d692
```

This will force the model to absorb this ObjectId and not generate a new one upon insertion.

#### Converting between types

MongoDB uses BSON types for data. This library holds a `MondocTypes` helper, which can assist in the conversion of these
native types.

```php
<?php
use \District5\Mondoc\Helper\MondocTypes;

// Dates
$mongoDateTime = MondocTypes::phpDateToMongoDateTime(new \DateTime());
$phpDateTime = MondocTypes::dateToPHPDateTime($mongoDateTime);

// BSON documents
$bsonDocument = new \MongoDB\Model\BSONDocument(['foo' => 'bar']);
$phpArrayFromDoc = MondocTypes::arrayToPhp($bsonDocument);

// BSON arrays
$bsonArray = new \MongoDB\Model\BSONArray(['foo', 'bar']);
$phpArrayFromArray = MondocTypes::arrayToPhp($bsonArray);

// ObjectIds
$anId = '61dfee5591efcf44e023d692';
$mongoId = MondocTypes::convertToMongoId($anId);
// You can also pass existing ObjectId's into the conversion and nothing happens.
// MondocTypes::convertToMongoId(new \MongoDB\BSON\ObjectId());
// MondocTypes::convertToMongoId($mongoId);
```

#### Query building

Query building is handled by the `MondocBuilder` library [https://github.com/district-5/php-mondoc-builder](https://github.com/district-5/php-mondoc-builder).

#### Testing

You can run PHPUnit against the library by running `composer install` and then running `phpunit`. Before doing so,
you'll need to copy the `example.phpunit.xml` to `phpunit.xml` and change the environment variables contained within.
