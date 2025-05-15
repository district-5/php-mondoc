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

namespace District5Tests\MondocTests\TestObjects\Model;

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocCloneableTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Class MyModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class MyModel extends MondocAbstractModel
{
    use MondocCloneableTrait;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $age = 0;

    private bool $_controlAsArray = false;

    /**
     * @return bool
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function incrementAge(): bool
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->inc('age', 1);
    }

    /**
     * @return bool
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     */
    public function decrementAge(): bool
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->dec('age', 1);
    }

    /**
     * This method must return the array to insert into Mongo.
     *
     * @param bool $withEncryption
     * @return array
     * @throws MondocConfigConfigurationException
     */
    public function asArray(bool $withEncryption = false): array
    {
        $this->assignDefaultVars();

        if ($this->_controlAsArray) {
            return [
                'name' => $this->getName(),
                'age' => $this->getAge(),
                'foo' => 'bar'
            ];
        }

        return parent::asArray($withEncryption);
    }

    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars(): void
    {
        // TODO: Implement
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return trim($this->name);
    }

    /**
     * @param string $val
     *
     * @return $this
     */
    public function setName(string $val): static
    {
        $this->name = trim($val);

        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     *
     * @return $this
     */
    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @param BSONArray|BSONDocument $bson
     * @return array
     */
    public function getArrayFromBson(BSONArray|BSONDocument $bson): array
    {
        return self::arrayToPhp($bson);
    }

    /**
     * @param array $array
     * @return array
     */
    public function getArrayFromArray(array $array): array
    {
        return self::arrayToPhp($array);
    }

    /**
     * Hack to allow it through.
     *
     * @param string $string
     * @param int $int
     * @return void
     */
    public function addUnmappedKey(string $string, int $int): void
    {
        $this->_mondocUnmapped[$string] = $int;
    }

    /**
     * Hack to allow it through for tests
     *
     * @param string $property
     * @return void
     */
    public function proxyAddDirty(string $property): void
    {
        $this->addDirty($property);
    }

    /**
     * Hack to allow it through for tests
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function proxySetKey(string $property, mixed $value): void
    {
        $this->__set($property, $value);
    }

    /**
     * @param bool $val
     * @return void
     */
    public function proxyControlAsArray(bool $val): void
    {
        $this->_controlAsArray = $val;
    }

    /**
     * @param array $val
     * @return array
     */
    public function proxyArrayToArray(array $val): array
    {
        return $this->arrayToArray($val);
    }

    /**
     * @param object $val
     * @return array
     */
    public function proxyObjectToArray(object $val): array
    {
        return $this->objectToArray($val);
    }
}
