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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use MongoDB\Database;

/**
 * Trait MondocConfigDatabaseTrait
 *
 * @package District5\Mondoc\Traits\Config
 */
trait MondocConfigDatabaseTrait
{
    /**
     * @var Database[]
     */
    protected array $databases = [];

    /**
     * @var string[]
     */
    protected array $databaseNames = [];

    /**
     * @param Database $database
     * @param string $connectionId
     *
     * @return $this
     */
    public function addDatabase(Database $database, string $connectionId = 'default'): static
    {
        $this->databases[$connectionId] = $database;
        $this->databaseNames[$connectionId] = $database->getDatabaseName();

        return $this;
    }

    /**
     * @param string $connectionId
     *
     * @return Database
     * @throws MondocConfigConfigurationException
     */
    public function getDatabase(string $connectionId = 'default'): Database
    {
        if (false === $this->hasConnectionId($connectionId)) {
            throw new MondocConfigConfigurationException(
                'MondocConfig: database connection not found for connection ID: ' . $connectionId
            );
        }

        return $this->databases[$connectionId];
    }

    /**
     * @param string $connectionId
     *
     * @return string
     * @throws MondocConfigConfigurationException
     */
    public function getDatabaseName(string $connectionId = 'default'): string
    {
        if (!array_key_exists($connectionId, $this->databaseNames)) {
            throw new MondocConfigConfigurationException(
                'MondocConfig: database name not resolvable. Missing connection ID: ' . $connectionId
            );
        }

        return $this->databaseNames[$connectionId];
    }

    /**
     * Does the connection ID exist?
     *
     * @param string $connectionId
     *
     * @return bool
     */
    abstract public function hasConnectionId(string $connectionId): bool;
}
