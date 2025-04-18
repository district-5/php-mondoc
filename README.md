Mondoc, by District5
====

[![CI](https://github.com/district-5/php-mondoc/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/district-5/php-mondoc/actions)
[![Latest Stable Version](http://poser.pugx.org/district5/mondoc/v)](https://packagist.org/packages/district5/mondoc)
[![PHP Version Require](http://poser.pugx.org/district5/mondoc/require/php)](https://packagist.org/packages/district5/mondoc)
[![Codecov](https://codecov.io/gh/district-5/php-mondoc/branch/master/graph/badge.svg)](https://codecov.io/gh/district-5/php-mondoc)

## The effortless MongoDB interaction layer for your PHP applications

### Installing with composer

```
composer require district5/mondoc
```

### Documentation...

All documentation for Mondoc is available at [mondoc.district5.dev](https://mondoc.district5.dev).

#### Common topics...

* Getting started: [mondoc.district5.dev/quick-start](https://mondoc.district5.dev/quick-start)
* Configuration: [mondoc.district5.dev/documentation/configuration](https://mondoc.district5.dev/documentation/configuration)
* Model: [mondoc.district5.dev/documentation/model](https://mondoc.district5.dev/documentation/model)
    * Nesting models: [mondoc.district5.dev/documentation/model/nested-models](https://mondoc.district5.dev/documentation/model/nested-models)
    * Helpful traits: [mondoc.district5.dev/documentation/model/traits](https://mondoc.district5.dev/documentation/model/traits)
* Service: [mondoc.district5.dev/documentation/service](https://mondoc.district5.dev/documentation/service)

## Important...

    As of version 7.0.0, version 2.0.0 of the MongoDB driver is supported. Requires district5/date: >=3.0.4

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

use District5\Mondoc\Db\Model\MondocAbstractModel;

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
    
    // This is optional, but can be used to map fields from the database to the model to keep keys short in storage.
    protected array $mondocFieldAliases = [
        'n' => 'name', // the `name` value would be stored in the database as the key `n`
    ];

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
        return $this;
    }
}
```

Additionally, in the model you can leverage the `$mondocFieldAliases` property to map fields from the database to the
model to keep keys short in storage. This is optional, but can be useful in some cases. An example of this is shown
below:

```php
namespace MyNs\Model;

use District5\Mondoc\Db\Model\MondocAbstractModel;

class MyModel extends MondocAbstractModel
{
    protected string $name = null;
    
    protected array $mondocFieldAliases = [
        'n' => 'name',
    ];

    // Rest of your model code...
}
```

##### Optional traits...

* `MondocVersionedModelTrait` - Version your data easily.
  * You can easily version data within a model by using the 
    `\District5\Mondoc\Db\Model\Traits\MondocVersionedModelTrait` trait. This trait introduces
    a `_v` variable in the model, which you can choose to increment when you choose.
    * You can detect if a model has a version by calling `isVersionableModel()` on the model.
* `MondocRevisionNumberTrait` - Adds an `_rn` property to a model to utilise as a revision
    number. This value is automatically set to `1` upon initial save of a model, and is
    incremented each time the model is updated. You can override this behaviour by manually
    assigning a value to the `_rn` property by calling `setRevisionNumber` method on your 
    inheriting model. This is different from the versioned model trait, as the revision number
    is incremented each time the model is updated, regardless of the changes made.
  * You can detect if a model has a revision number by calling `isRevisionNumberModel()` on the
    model.
* `MondocCreatedDateTrait` - Adds a `cd` property to a model to utilise as a created date.
  * This value is automatically assigned to the current UTC date upon initial save of a model,
    or if an existing model is updated and the `cd` property has not been set. You can 
    override this behaviour by assigning a value to the `cd` property.
* `MondocModifiedDateTrait` - Adds a `md` property to a model to utilise as an updated date.
  * This value is automatically assigned the current UTC date, but you can override this 
    behaviour by assigning a value to the `md` property prior to saving.
* `MondocCloneableTrait` - Adds a `clone` method to the model, which will return a new
    instance of the model with the same properties as the original. Optionally, when calling
    `->clone` you can pass a boolean to indicate if you want to persist the new model to the
    database. The optional second parameter is the object or class to clone to. For example,
    you can make a clone of `MyModel` and convert it to `OtherModel` by calling
    `$myModel->clone( < save:bool > , OtherModel::class)`.
* `MondocRetentionTrait` - Adding this to your model exposes the `setMondocRetentionChangeMeta`
    and `setMondocRetentionExpiry` methods, which allows you to set the retention data for the
    model. This is useful for setting things such as the retention period and the retention policy.

**Traits examples**

```php
<?php
class MyModel extends \District5\Mondoc\Db\Model\MondocAbstractModel
{
    use \District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocCloneableTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocRevisionNumberTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocVersionedModelTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocRetentionTrait;
    
    // Rest of your model code...
}
```

#### The service layer

The logic for querying the database is always performed in the service layer. There's only a single required method,
`getCollectionName`, which should return the name of the collection in the database.

Optionally, you can define a `getConnectionId` method to return the connection ID to use from the `MondocConfig`
connection manager. This is useful if you're using multiple connections, for example, a connection for authentication
and a connection for the main application.

All Mondoc native queries automatically convert `DateTime` objects to `UTCDateTime` objects when querying a collection.

> **Please note**: in versions prior to 6.3.0 the `PaginationTrait` required you to pass the filter into each method
> call. This is no longer required, as the filter is carried through by the `MondocPaginationHelper` object now.

```php
<?php
namespace Myns\Service;

use MyNs\Model\MyModel;
use District5\Mondoc\Db\Service\MondocAbstractService

/**
 * Class MyService
 * @package MyNs\Service
 */
class MyService extends MondocAbstractService
{
    /**
     * Get the collection name.
     *
     * @return string
     */
    protected static function getCollectionName(): string
    {
        return 'users';
    }
    
    /**
     * Get the connection ID to use from the MondocConfig manager. Defaults to 'default'.
     * This method isn't required if you're using the default connection, but if you're using
     * multiple connections, you can use this method to return the connection ID.
     *
     * @return string
     */
    protected static function getConnectionId() : string
    {
        return 'default'; // this is the default connection.
    }
}
```

#### Nesting objects

You can nest objects in each other. The main model must extend `\District5\Mondoc\Db\Model\MondocAbstractModel` and have
the sub models defined in the `$mondocNested` array.

Sub models must extend `\District5\Mondoc\Db\Model\MondocAbstractSubModel`.

When implementing `$mondocNested`, you declare a single nested model, or an array of nested models. For example:

```php
protected Food $favouriteFood;
protected array $allFoods; // Array of 'Food' objects

protected array $mondocNested = [
    'favouriteFood' => Food::class, // Single nested model
    'allFoods' => Food::class . '[]' // Array of nested models
];
```

 > **Please note**: in versions prior to 5.1.0 any nested property was required to have `BSONDocument` or `BSONArray` as
 > part of the property definition. This is no longer required as the library will automatically inflate the class(es)
 > correctly

Nested objects, regardless of depths, can also take advantage of the `$mondocFieldAliases` property to map fields from the
database to the model. This keeps the keys short in storage, while allowing for longer, more descriptive keys in the
model. For the above example, you could have the following:

```php
protected Food $favouriteFood;
protected array $allFoods; // Array of 'Food' objects

protected array $mondocFieldAliases = [
    'ff' => 'favouriteFood',
    'af' => 'allFoods'
];
```

```php
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\MondocAbstractSubModel;

class FavouriteFood extends MondocAbstractSubModel
{
    protected string $foodName;

    protected string $foodDescription;

    // This is optional, but can be used to map fields from the database to the model to keep keys short in storage
    protected array $mondocFieldAliases = [
        'fd' => 'foodDescription',
    ];

    public function getFoodName()
    {
        return $this->foodName;
    }

    public function getFoodDescription()
    {
        return $this->foodDescription;
    }
}

class Car extends MondocAbstractSubModel
{
    protected string|null $brand = null;
    protected string|null $colour = null;
    
    public function getBrand(): ?string
    {
        return $this->brand;
    }
    
    public function getColour(): ?string
    {
        return $this->colour;
    }
}

class Person extends MondocAbstractModel
{
    /**
     * @var string|null
     */
    protected string|null $name = null;
    
    /**
     * @var FavouriteFood 
     */
    protected FavouriteFood|null $favouriteFood = null; // Having BSONDocument here is important as inflation will use the property
    
    /**
     * @var FavouriteFood[]
     */
    protected array $allFoods = []; // Having BSONArray here is important as inflation will use the property
    
    /**
     * @var Car 
     */
    protected Car|null $car = null; // Having BSONDocument here is important as inflation will use the property
    
    /**
     * @var string[] 
     */
    protected array $mondocNested = [
        'allFoods' => FavouriteFood::class . '[]', // Indicates an array of FavouriteFood objects
        'favouriteFood' => FavouriteFood::class,
        'car' => Car::class
    ];

    public function getAllFoods(): array
    {
        return $this->allFoods;
    }

    public function getFavouriteFoodName(): ?string
    {
        return $this->favouriteFood->getFoodName();
    }

    public function getCarBrand(): ?string
    {
        return $this->car->getBrand();
    }

    public function getCarColour(): ?string
    {
        return $this->car->getColour();
    }
}
```

#### Finding documents..

```php
<?php

// count documents matching a filter
\District5Tests\MondocTests\TestObjects\MyService::countAll([], []);
// count documents using a query builder
$builder = \District5Tests\MondocTests\TestObjects\MyService::getQueryBuilder();
\District5Tests\MondocTests\TestObjects\MyService::countAllByQueryBuilder($builder);

// get single model by id, accepts a string or ObjectId
\District5Tests\MondocTests\TestObjects\MyService::getById('the-mongo-id');

// get multiple models by ids. accepts string or ObjectIds
\District5Tests\MondocTests\TestObjects\MyService::getByIds(['an-id', 'another-id']);

// get single model with options
\District5Tests\MondocTests\TestObjects\MyService::getOneByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// get multiple models with options
\District5Tests\MondocTests\TestObjects\MyService::getMultiByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// working with dates, both of these queries are the same
$phpDate = new \DateTime();
\District5Tests\MondocTests\TestObjects\MyService::getMultiByCriteria(['dateField' => ['$lte' => $phpDate]]);
$mongoDate = \District5\Mondoc\Helper\MondocTypes::phpDateToMongoDateTime($phpDate);
\District5Tests\MondocTests\TestObjects\MyService::getMultiByCriteria(['dateField' => ['$lte' => $mongoDate]]);

// paginating results by page number
$currentPage = 1;
$perPage = 10;
$sortByField = 'foo';
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelper($currentPage, $perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPage($pagination, $sortByField, $sortDirection); // Since 6.3.0 the filter is carried through by the pagination helper

// paginating results by ID number descending (first page)
$currentId = null;
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection); // Since 6.3.0 the filter is carried through by the pagination helper

// paginating results by ID number descending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection); // Since 6.3.0 the filter is carried through by the pagination helper

// paginating results by ID number ascending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = 1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection); // Since 6.3.0 the filter is carried through by the pagination helper

// get the distinct values for 'age' with a filter and options
\District5Tests\MondocTests\TestObjects\MyService::getDistinctValuesForKey('age', ['foo' => 'bar'], ['sort' => ['age' => 1]]);

// average age with filter
\District5Tests\MondocTests\TestObjects\MyService::aggregate()->getAverage('age', ['foo' => 'bar']);

// 10% percentile, sorted asc with filter
\District5Tests\MondocTests\TestObjects\MyService::aggregate()->getPercentile('age', 0.1, 1, ['foo' => 'bar']);

// get sum of a field with a given filter
\District5Tests\MondocTests\TestObjects\MyService::aggregate()->getSum('age', ['foo' => 'bar']);

// get the min value of a field with a given filter
\District5Tests\MondocTests\TestObjects\MyService::aggregate()->getMin('age', ['foo' => 'bar']);
// ...or with a string...
// \District5Tests\MondocTests\TestObjects\MyService::aggregate()->getMin('name', ['foo' => 'bar']);

// get the max value of a field with a given filter
\District5Tests\MondocTests\TestObjects\MyService::aggregate()->getMax('age', ['foo' => 'bar']);
// ...or with a string...
// \District5Tests\MondocTests\TestObjects\MyService::aggregate()->getMax('name', ['foo' => 'bar']);
```


#### Model to array...

You can export a model to an array by calling [`asArray()`](./src/Db/Model/MondocAbstractSubModel.php) on the model.
This will return an array of the model's properties.

The properties returned by the [`asArray()`](./src/Db/Model/MondocAbstractSubModel.php) method are the properties and
types that have been set on the model, which means they're likely not able to be directly encoded to JSON. To get around
this, you can call [`asJsonEncodableArray()`](./src/Db/Model/MondocAbstractSubModel.php) on the model, which will return an array that can be encoded to JSON.
Optionally, you can provide a list of fields to omit from the returned array.

```php
/* @var $model \District5\Mondoc\Db\Model\MondocAbstractModel */

$mongoInsertionDocument = $model->asArray(); // Not encodable to JSON
$jsonEncodable = $model->asJsonEncodableArray(); // Encodable to JSON
$jsonEncodable = $model->asJsonEncodableArray(['password', 'secret']); // Encodable to JSON omitting the 'password' and 'secret' properties

$encodedJson = json_encode($jsonEncodable, JSON_THROW_ON_ERROR);
echo $encodedJson;
```


#### Useful information...

To use a pre-determined ObjectId as the document `_id`, you can call `setPresetObjectId` against the model. This will
force the model to absorb this ObjectId and not generate a new one upon insertion. For example:

```php
<?php
/** @noinspection SpellCheckingInspection */
$theId = new \MongoDB\BSON\ObjectId('61dfee5591efcf44e023d692');

$person = new Person();
$person->setPresetObjectId(new ObjectId());
$insertOrUpdateOptions = [];
$person->save($insertOrUpdateOptions); // optional

echo $person->getObjectIdString(); // 61dfee5591efcf44e023d692
```

Additionally, there's a method called `assignDefaultVars` which can be used to assign default values to the model's
properties. This is useful for setting default values for properties. The call to `assignDefaultVars` occurs AFTER
inflation has occurred, so it's important to note that the properties may already have values assigned. For example:

```php
<?php
use District5\Mondoc\Db\Model\MondocAbstractModel;

class MyModel extends MondocAbstractModel
{
    protected string $name = null;
    protected int $version = 0;

    protected function assignDefaultVars()
    {
        if ($this->version < 1) {
            $this->version = 1;
        }
    }
}
```

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
/** @noinspection SpellCheckingInspection */
$anId = '61dfee5591efcf44e023d692';
$objectId = MondocTypes::toObjectId($anId);
// You can also pass existing ObjectId's into the conversion and nothing happens.
// MondocTypes::toObjectId(new \MongoDB\BSON\ObjectId());
// MondocTypes::toObjectId($objectId);

$string = MondocTypes::objectIdToString($objectId);

// less used, but still handled...
$objectId = MondocTypes::toObjectId([
    '$oid' => '61dfee5591efcf44e023d692'
]);
$objectId = MondocTypes::toObjectId([
    'oid' => '61dfee5591efcf44e023d692'
]);
```

#### Retention of data

When using the `MondocRetentionTrait` trait, you can set the retention data for a model by calling
`setMondocRetentionChangeMeta`. There is no preset retention data, so you must set this yourself. This is useful for
setting things such as the user's name who initiated the change, or similar for compliance and the retention policy.
Additionally, the method `setMondocRetentionExpiry` method is exposed, and can be used to set the expiry date for the
retention data. The library does not automatically delete the data when the retention period has expired, but you can
use the `MondocRetentionService` to query for data that has expired, using either 
`getPaginatorForExpiredRetentionForClassName` or `getPaginatorForExpiredRetentionForObject`, and then subsequently the
`getRetentionPage` method.

Exposed methods in the `MondocRetentionService`:

* `create` - Create a new retention model. This is called automatically by Mondoc when a model contains the
    `MondocRetentionTrait` trait.
* `createStub` - Create a new retention model, but don't save it. This is useful for creating a retention model that
    you want to save at a later date. This is used when inserting multiple models.
* `getLatestRetentionModelForModel` - Get the latest retention model for a given (previously saved) model.
* `countRetentionModelsForClassName` - Count the number of retention models for a given class name.
* `countRetentionModelsForModel` - Count the number of retention models for a given (previously saved) model.
* `getRetentionHistoryPaginationHelperForClassName` - Get a pagination helper for the retention history for a given
    class name.
* `getRetentionHistoryPaginationHelperForModel` - Get a pagination helper for the retention history for a given
    (previously saved) model.
* `addIndexes` - Add the indexes to the retention collection. This is NOT called automatically by Mondoc, and must
    be called manually by your application.
* `hasIndexes` - Check if the indexes have been added to the retention collection. This is a helper method to allow
    you to check if the indexes have been added, and if not, you can call `addIndexes` to add them.
* `getPaginatorForExpiredRetentionForClassName` - Get a pagination helper for the retention history for a given class
    name, where the retention has expired.
* `getPaginatorForExpiredRetentionForObject` - Get a pagination helper for the retention history for a given (previously
    saved) model, where the retention has expired.
* `getRetentionPage` - Get a page of retention models from the pagination helper. This is the method you use after
    retrieving a pagination helper via `getPaginatorForExpiredRetentionForClassName` or
    `getPaginatorForExpiredRetentionForObject`.

Within a `MondoRetentionModel`, the following methods are available:

* `toOriginalModel` - Get the original model that the retention data is associated with. This will return the model
    inflated with the data that was saved at the time of the retention data being saved.
* `getSourceModelData` - Get the data that was saved at the time of the retention data being saved. This will return
    the data as it was saved at the time of the retention data being saved, in array format.
* `getSourceObjectId` - Get the ObjectId of the original model that the retention data is associated with.
* `getSourceObjectIdString` - Get the ObjectId of the original model that the retention data is associated with, as a
    string.
* `getSourceClassName` - Get the class name of the original model that the retention data is associated with.
* `getRetentionData` - Get the retention data that was saved at the time of the retention data being saved, as set by
    the original call to `setMondocRetentionChangeMeta`, contained in the `MondocRetentionTrait`.
* `getRetentionExpiry` - Get the retention expiry date that was saved at the time of the retention data being saved, as
    set by the original call to `setMondocRetentionExpiry`, contained in the `MondocRetentionTrait`.
* `hasRetentionExpired` - Check if this retention model has expired. This will return `true` if the retention data has
    expired, and `false` if it has not.

A working example of using the retention trait is shown below:

```php
<?php

use District5\Date\Date;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocRetentionTrait;
use District5\Mondoc\Helper\MondocTypes;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;

class MyService extends MondocAbstractService
{
    protected static function getCollectionName(): string
    {
        return 'data';
    }
}

class MyModel extends MondocAbstractModel
{
    use MondocRetentionTrait;

    protected string $name = null;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}

$model = new MyModel();
$model->setName('John Doe');
$model->setMondocRetentionData([
    'user' => 'joe.bloggs',
]);
$model->setMondocRetentionExpiry(
    Date::modify(
        Date::nowUtc()
    )->plus()->days(30)
);
$model->save();

// There is now both a `MyModel` saved, and a `MondocRetentionModel` saved with the retention data.
$retrieved = MyService::getById($model->getObjectIdString());
$retrieved->setMondocRetentionData([
    'user' => 'jane.bloggs',
]);
$retrieved->setMondocRetentionExpiry(
    null // this data will never expire
);
$retrieved->save();

// A new `MondocRetentionModel` is saved with the updated retention data. There are now two `MondocRetentionModel`'s

$paginator = MondocRetentionService::getRetentionHistoryPaginationHelperForClassName(
    MyModel::class,
    1,
    10,
    ['user' => 'joe.bloggs']
);
$results = MondocRetentionService::getPage(
    $paginator // The filter is carried through by the pagination helper
);
// This will return the `MongoRetentionModel` for the `MyModel` with the user `joe.bloggs`
$firstResultInflated = $results[0]->toOriginalModel();
echo $firstResultInflated->getName(); // John Doe
```

#### Query building

Query building is handled by the `MondocBuilder` library [https://github.com/district-5/php-mondoc-builder](https://github.com/district-5/php-mondoc-builder).

The query builder does not take into account for the model's properties, but rather the database's properties. This
means that it will not listen or adhere to any field mappings that have been set on the model.

For example, this map would require the query builder to use the `n` key, not `name`:

```php
<?php
use District5\Mondoc\Db\Model\MondocAbstractSubModel;

class MyModel extends MondocAbstractSubModel
{
    protected array $mondocFieldAliases = [
        'n' => 'name',
    ];
    
    // Rest of your model code...
}

$wontWork = new \District5\Mondoc\Db\Builder\MondocBuilder\MondocBuilder();
$wontWork->addFilter('name', 'John'); // This WILL NOT WORK with the field mapping

$willWork = new \District5\Mondoc\Db\Builder\MondocBuilder\MondocBuilder();
$willWork->addFilter('n', 'John'); // This will work with the field mapping
```


#### Testing

You can run PHPUnit against the library by running `composer install` and then running `phpunit`. Before doing so,
you'll need to assign the `MONGO_CONNECTION_STRING` environment variable to a valid MongoDB connection string.
