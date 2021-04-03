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
use District5\Mondoc\MondocConnections;
use MongoDB\Client;

$connection = new Client('< mongo connection string >');
$database = $connection->selectDatabase('< database name >');

/** @noinspection PhpRedundantOptionalArgumentInspection */
MondocConnections::getInstance()->setDatabase(
    $database,
    'default' // a connection identifier ('default' is the default value).
);
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
     * @return bool
     */
    public function save(): bool
    {
        /** @noinspection PhpUndefinedClassInspection */
        return MyService::saveModel($this);
    }
    
    /**
     * @return bool
     */
    public function delete(): bool
    {
        /** @noinspection PhpUndefinedClassInspection */
        return MyService::deleteModel($this);
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

```php
<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
\MyNs\Service\MyService::getById('the-mongo-id'); // accepts a string or ObjectId
```

#### Query building

Query building is handled by the `MondocBuilder` library [https://github.com/district-5/php-mondoc-builder](https://github.com/district-5/php-mondoc-builder).
