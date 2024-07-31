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

namespace District5\Mondoc;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
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
     * @param Database $database
     * @param string $connectionId
     *
     * @return $this
     */
    public function addDatabase(Database $database, string $connectionId = 'default'): MondocConfig
    {
        $this->databases[$connectionId] = $database;

        return $this;
    }

    /**
     * @param string $name
     * @param string $connectionId
     *
     * @return Collection
     * @throws MondocConfigConfigurationException
     */
    public function getCollection(string $name, string $connectionId = 'default'): Collection
    {
        return $this->getDatabase(
            $connectionId
        )->selectCollection(
            $name
        );
    }

    /**
     * @param string $connectionId
     *
     * @return Database
     * @throws MondocConfigConfigurationException
     */
    public function getDatabase(string $connectionId = 'default'): Database
    {
        if (!array_key_exists($connectionId, $this->databases)) {
            throw new MondocConfigConfigurationException(
                'MondocConfig: database connection not found for connection ID: ' . $connectionId
            );
        }

        return $this->databases[$connectionId];
    }

    /**
     * Set the whole service map. This replaces the existing service map.
     *
     * @param array $serviceMap
     * @return $this
     */
    public function setServiceMap(array $serviceMap): MondocConfig
    {
        $this->serviceMap = $serviceMap;
        return $this;
    }

    /**
     * Order is
     *      ModelFQCN => ServiceFQCN,
     *      AnotherModelFQCN => AnotherServiceFQCN,
     *      ...
     * @return string[]
     */
    public function getServiceMap(): array
    {
        return $this->serviceMap;
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
     * @return string
     * @throws MondocServiceMapErrorException
     */
    public function getServiceForModel(string $modelFQCN): string
    {
        if (!array_key_exists($modelFQCN, $this->serviceMap)) {
            throw new MondocServiceMapErrorException(
                'MondocConfig: service not found for model: ' . $modelFQCN
            );
        }
        return $this->serviceMap[$modelFQCN];
    }

    /**
     * @param string $serviceFQCN
     * @return string|null
     * @throws MondocServiceMapErrorException
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

        throw new MondocServiceMapErrorException(
            'MondocConfig: model not found for service: ' . $serviceFQCN
        );
    }

    /**
     * @return MondocConfig
     */
    public function reconstruct(): MondocConfig
    {
        self::$_instance = null;
        return self::getInstance();
    }
}
