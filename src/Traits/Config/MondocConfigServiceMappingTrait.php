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

namespace District5\Mondoc\Traits\Config;

use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Extensions\Retention\MondocRetentionModel;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;

/**
 * Trait MondocConfigServiceMappingTrait
 *
 * @package District5\Mondoc\Traits\Config
 */
trait MondocConfigServiceMappingTrait
{
    /**
     * @var string[]
     */
    protected array $serviceMap = [];

    /**
     * @var string[]
     */
    private array $establishedModels = [];

    /**
     * Set the whole service map. This replaces the existing service map.
     *
     * @param array $serviceMap
     * @return $this
     */
    public function setServiceMap(array $serviceMap): static
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
    protected function getExtensionsServiceMap(): array
    {
        return [
            MondocRetentionModel::class => MondocRetentionService::class,
        ];
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
    public function addServiceMapping(string $modelFQCN, string $serviceFQCN): static
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
        $allServiceMap = array_merge($this->getServiceMap(), $this->getExtensionsServiceMap());

        if (!array_key_exists($modelFQCN, $allServiceMap)) {
            throw new MondocServiceMapErrorException(
                'MondocConfig: service not found for model: ' . $modelFQCN
            );
        }
        return $allServiceMap[$modelFQCN];
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

        $allServiceMap = array_merge($this->getServiceMap(), $this->getExtensionsServiceMap());

        foreach ($allServiceMap as $modelFQCN => $serviceFQCN2) {
            if ($serviceFQCN === $serviceFQCN2) {
                $this->establishedModels[$serviceFQCN] = $modelFQCN;
                return $modelFQCN;
            }
        }

        throw new MondocServiceMapErrorException(
            'MondocConfig: model not found for service: ' . $serviceFQCN
        );
    }
}
