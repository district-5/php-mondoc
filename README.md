District5 - Mondoc
======

![CI](https://github.com/district-5/php-mondoc/actions/workflows/ci.yml/badge.svg?branch=master)

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
        $this->addDirty('name');
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

* `MondocVersionedModelTrait` - You can easily version data within a model by using the
  `\District5\Mondoc\Db\Model\Traits\MondocVersionedModelTrait` trait. This trait introduces
  a `_v` variable in the model, which you can choose to increment when you choose.
  * You can detect if a model has a version by calling `isVersionableModel()` on the model.
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

**Traits examples**

```php
<?php
class MyModel extends \District5\Mondoc\Db\Model\MondocAbstractModel
{
    use \District5\Mondoc\Db\Model\Traits\MondocVersionedModelTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;
    use \District5\Mondoc\Db\Model\Traits\MondocCloneableTrait;
    
    // Rest of your model code...
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

The logic for querying the database etc., is always performed in the service layer.

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

// paginating results by page number
$currentPage = 1;
$perPage = 10;
$sortByField = 'foo';
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelper($currentPage, $perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPage($pagination, $perPage, ['foo' => 'bar'], $sortByField, $sortDirection);

// paginating results by ID number descending (first page)
$currentId = null;
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

// paginating results by ID number descending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = -1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

// paginating results by ID number ascending
$currentId = '5f7deca120c41f29827c0c60'; // or new ObjectId('5f7deca120c41f29827c0c60');
$perPage = 10;
$sortDirection = 1;
$pagination = \District5Tests\MondocTests\TestObjects\MyService::getPaginationHelperForObjectIdPagination($perPage, ['foo' => 'bar'])
$results = \District5Tests\MondocTests\TestObjects\MyService::getPageByByObjectIdPagination($pagination, $currentId, $perPage, $sortDirection, ['foo' => 'bar']);

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
