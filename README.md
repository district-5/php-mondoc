District5 - Mondoc
======

### Composer...

In your `composer.json` file include:

```json
{
    "repositories":[
        {
            "type": "vcs",
            "url": "git@github.com:district-5/php-mondoc.git"
        }
    ],
    "require": {
        "php": ">=7.1",
        "district5/mondoc": ">=0.0.1",
        "mongodb/mongodb": "^1.5",
        "ext-mongodb": "*"
    },
    "autoload" : {
        "psr-0" : {
            "MyNs" : "lib/"
        }
    }
}
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
$config->setDatabase(
    $database,
    'default' // a connection identifier ('default' is the default value).
);
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
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace MyNs\Model;

use District5\Mondoc\Model\MondocAbstractModel;
use MyNs\Service\MyService;

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
        $this->addDirty('name', trim($val));
        return $this;
    }
    
    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars()
    {
        // TODO: Implement
    }


    /**
     * This method must return the array to insert into Mongo.
     *
     * @return array
     */
    public function asArray(): array
    {
        $this->assignDefaultVars();
        return [
            'name' => $this->getName()
        ];
    }
}
```

#### The service layer

```php
<?php/** @noinspection PhpUndefinedClassInspection *//** @noinspection PhpUndefinedNamespaceInspection */
namespace Myns\Service;

use MyNs\Model\MyModel;

/**
 * Class MyService
 * @package MyNs\Service
 */
class MyService extends AbstractService
{
    /**
     * @var string
     */
    protected static $modelClassName = MyModel::class;

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

You can nest objects in each other. The main model must extend `\District5\Mondoc\Model\MondocAbstractModel` and have the sub
models defined in the `$keyToClassMap` array.

Sub models must extend `\District5\Mondoc\Model\MondocAbstractSubModel`.

```php
use District5\Mondoc\Model\MondocAbstractModel;
use District5\Mondoc\Model\MondocAbstractSubModel;

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
    protected $keyToClassMap = [
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

// get single model by id, accepts a string or ObjectId
\District5Tests\MondocTests\Example\MyService::getById('the-mongo-id');

// get multiple models by ids. accepts string or ObjectIds
\District5Tests\MondocTests\Example\MyService::getByIds(['an-id', 'another-id']);

// get single model with options
\District5Tests\MondocTests\Example\MyService::getOneByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// get multiple models with options
\District5Tests\MondocTests\Example\MyService::getMultiByCriteria(['foo' => 'bar'], ['sort' => ['foo' => -1]]);

// get the distinct values for 'age' with a filter and options
\District5Tests\MondocTests\Example\MyService::getDistinctValuesForKey('age', ['foo' => 'bar'], ['sort' => ['age' => 1]]);

// average age with filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getAverage('age', ['foo' => 'bar']);

// 10% percentile, sorted asc with filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getPercentile('age', 0.1, 1, ['foo' => 'bar']);

// get sum of a field with a given filter
\District5Tests\MondocTests\Example\MyService::aggregate()->getSum('age', ['foo' => 'bar']);
```

#### Query building

Query building is handled by the `MondocBuilder` library [https://github.com/district-5/php-mondoc-builder](https://github.com/district-5/php-mondoc-builder).