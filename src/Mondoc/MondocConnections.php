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
 * Class MondocConnections.
 *
 * @package District5\Mondoc
 */
class MondocConnections
{
    /**
     * Static variable, holding the instance of this Singleton.
     *
     * @var MondocConnections
     */
    protected static $_instance = null;

    /**
     * @var Database[]
     */
    protected $databases = [];

    /**
     * MondocConnections constructor. Protected to avoid direct construction.
     */
    protected function __construct()
    {
    }

    /**
     * @param Database $database
     * @param string   $key
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function setDatabase(Database $database, $key = 'default'): MondocConnections
    {
        $this->databases[$key] = $database;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return null|Database
     */
    public function getDatabase(string $key = 'default'): ?Database
    {
        if (array_key_exists($key, $this->databases)) {
            return $this->databases[$key];
        }

        return null;
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
     * Retrieve an instance of this object.
     *
     * @return MondocConnections
     */
    public static function getInstance(): MondocConnections
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
