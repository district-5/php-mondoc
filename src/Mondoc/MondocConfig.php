<?php

/**
 * District5 - Mondoc
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 */

namespace District5\Mondoc;

use MongoDB\Collection;
use MongoDB\Database;

/**
 * Class MondocConfig.
 *
 * @package District5\Mondoc
 */
class MondocConfig
{
    /**
     * Static variable, holding the instance of this Singleton.
     *
     * @var MondocConfig|null
     */
    protected static ?MondocConfig $_instance = null;

    /**
     * @var Database[]
     */
    protected array $databases = [];

    /**
     * @var string[]
     */
    protected array $serviceMap = [];

    /**
     * @var string[]
     */
    private array $establishedModels = [];

    /**
     * MondocConfig constructor. Protected to avoid direct construction.
     */
    protected function __construct()
    {
    }

    /**
     * Retrieve an instance of this object.
     *
     * @return MondocConfig
     */
    public static function getInstance(): MondocConfig
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @deprecated
     * @see MondocConfig::addDatabase()
     * @noinspection PhpUnused
     */
    public function setDatabase(Database $database, string $key = 'default'): MondocConfig
    {
        return $this->addDatabase($database, $key);
    }

    /**
     * @param Database $database
     * @param string $key
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function addDatabase(Database $database, string $key = 'default'): MondocConfig
    {
        $this->databases[$key] = $database;

        return $this;
    }

    /**
     * @param string $name
     * @param string $key
     *
     * @return null|Collection
     */
    public function getCollection(string $name, string $key = 'default'): ?Collection
    {
        if (null !== $database = $this->getDatabase($key)) {
            return $database->selectCollection($name);
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return null|Database
     * @noinspection PhpUnused
     */
    public function getDatabase(string $key = 'default'): ?Database
    {
        if (array_key_exists($key, $this->databases)) {
            return $this->databases[$key];
        }

        return null;
    }

    /**
     * Set the whole service map. This replaces the existing service map.
     *
     * @param array $serviceMap
     * @return $this
     * @noinspection PhpUnused
     */
    public function setServiceMap(array $serviceMap): MondocConfig
    {
        $this->serviceMap = $serviceMap;
        return $this;
    }

    /**
     * @param string $modelFQCN
     * @param string $serviceFQCN
     * @return $this
     */
    public function addServiceMapping(string $modelFQCN, string $serviceFQCN): MondocConfig
    {
        $this->serviceMap[$modelFQCN] = $serviceFQCN;
        return $this;
    }

    /**
     * @param string $modelFQCN
     * @return string|null
     */
    public function getServiceForModel(string $modelFQCN): ?string
    {
        if (array_key_exists($modelFQCN, $this->serviceMap)) {
            return $this->serviceMap[$modelFQCN];
        }
        return null;
    }

    /**
     * @param string $serviceFQCN
     * @return string|null
     */
    public function getModelForService(string $serviceFQCN): ?string
    {
        if (array_key_exists($serviceFQCN, $this->establishedModels)) {
            return $this->establishedModels[$serviceFQCN];
        }

        foreach ($this->serviceMap as $modelFQCN => $serviceFQCN2) {
            if ($serviceFQCN === $serviceFQCN2) {
                $this->establishedModels[$serviceFQCN] = $modelFQCN;
                return $modelFQCN;
            }
        }
        return null;
    }
}
