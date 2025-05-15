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
use District5\Mondoc\Traits\Config\MondocConfigDatabaseTrait;
use District5\Mondoc\Traits\Config\MondocConfigEncryptionTrait;
use District5\Mondoc\Traits\Config\MondocConfigServiceMappingTrait;
use MongoDB\Collection;

/**
 * Class MondocConfig.
 *
 * @package District5\Mondoc
 */
class MondocConfig
{
    use MondocConfigDatabaseTrait;
    use MondocConfigEncryptionTrait;
    use MondocConfigServiceMappingTrait;

    /**
     * Static variable, holding the instance of this Singleton.
     *
     * @var MondocConfig|null
     */
    protected static ?MondocConfig $_instance = null;

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
     * Does the connection ID exist?
     *
     * @param string $connectionId
     *
     * @return bool
     */
    public function hasConnectionId(string $connectionId): bool
    {
        return array_key_exists($connectionId, $this->databases);
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
