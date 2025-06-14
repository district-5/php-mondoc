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

namespace District5\Mondoc\Db\Model;

use DateTime;
use District5\Mondoc\Db\Model\Traits\Static\ExcludedPropertiesTrait;
use District5\Mondoc\Db\Model\Traits\Static\FieldAliasMapTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocEncryptedFieldsTrait;
use District5\Mondoc\Db\Model\Traits\Static\MondocNestedModelTrait;
use District5\Mondoc\Db\Model\Traits\Static\UnmappedPropertiesTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Helper\MondocTypes;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Abstract class MondocAbstractSubModel.
 *
 * @package District5\Mondoc\Db\Model
 */
abstract class MondocAbstractSubModel
{
    use ExcludedPropertiesTrait;
    use FieldAliasMapTrait;
    use MondocEncryptedFieldsTrait;
    use MondocNestedModelTrait;
    use UnmappedPropertiesTrait;

    /**
     * MondocAbstractSubModel constructor.
     */
    public function __construct()
    {
        $this->initMondocNestedModels();
        $this->initMondocFieldAliases();
    }

    /**
     * Inflate an array of DTOs from an array of arrays containing data.
     *
     * @param array $data
     *
     * @return $this[]
     *
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @example
     * MyModel::inflateMultipleArrays(
     *      [
     *          [
     *              'name' => 'Joe',
     *              'age' => 30
     *          ],
     *          [
     *              'name' => 'Jane',
     *              'age' => 32
     *          ]
     *      ]
     * )
     */
    public static function inflateMultipleArrays(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        $cl = get_called_class();
        /* @var $cl MondocAbstractSubModel - it's not really. */
        $final = [];
        foreach ($data as $datum) {
            if ($datum instanceof BSONDocument) {
                $datum = $datum->getArrayCopy();
            }
            $inst = $cl::inflateSingleArray($datum);
            $inst->assignDefaultVars();
            $final[] = $inst;
        }

        return $final;
    }

    /**
     * Assign any default variables to this model.
     */
    protected function assignDefaultVars(): void
    {
    }

    /**
     * Inflate a DTO from an array of data.
     *
     * @param array $data
     *
     * @return $this
     * @throws MondocConfigConfigurationException
     * @throws \District5\MondocEncryption\Exception\MondocEncryptionException
     * @throws MondocException
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function inflateSingleArray(array $data): static
    {
        $cl = get_called_class();
        $inst = new $cl();
        /* @var $inst MondocAbstractSubModel */

        foreach ($data as $k => $v) {
            if (is_int($k)) {
                continue;
            }
            if (in_array($k, $inst->getPropertyExclusions())) {
                continue;
            }
            $k = $inst->getFieldAliasMapLocalName($k);
            $v = $inst->decryptMondocField($k, $v);
            if ($v instanceof UTCDateTime) {
                $v = MondocTypes::dateToPHPDateTime($v);
            }

            $isNestedAny = $inst->isMondocNestedAnyType($k);
            if ((is_array($v) || is_object($v)) && $isNestedAny === true) {
                $subClassName = $inst->getMondocNestedClassName($k);
                $isMulti = $inst->isMondocNestedMultipleObjects($k);
                if (is_object($v)) {
                    $v = MondocTypes::arrayToPhp($v);
                }
                /* @var $subClassName MondocAbstractSubModel */
                if ($isMulti === true) {
                    $v = $subClassName::inflateMultipleArrays($v);
                } else {
                    $v = $subClassName::inflateSingleArray($v);
                }
            }
            if ($isNestedAny === false && ($v instanceof BSONDocument || $v instanceof BSONArray)) {
                $v = $v->getArrayCopy();
            }

            $inst->__set($k, $v);
        }

        return $inst;
    }

    /**
     * Helper method to convert an object (with potential arrays or objects inside) into a normal array.
     *
     * @param object $obj
     *
     * @return array
     */
    protected static function objectToArray(object $obj): array
    {
        $props = get_object_vars($obj);
        $array = [];
        foreach ($props as $k => $v) {
            if (is_array($v)) {
                $v = self::arrayToArray($v);
            }
            if (is_object($v)) {
                $v = self::objectToArray($v);
            }
            $array[$k] = $v;
        }

        return $array;
    }

    /**
     * Helper method to convert an array (with potential objects) into a normal array.
     *
     * @param array $arr
     *
     * @return array
     */
    protected static function arrayToArray(array $arr): array
    {
        $data = [];
        $isIntArray = false;
        $i = 0;
        foreach ($arr as $k => $v) {
            if ($k === $i) {
                $isIntArray = true;
            }
            if (is_array($v)) {
                $v = self::arrayToArray($v);
            }
            if (is_object($v)) {
                $v = self::objectToArray($v);
            }
            if (true === $isIntArray) {
                $data[] = $v;
            } else {
                $data[$k] = $v;
            }
            ++$i;
        }

        return $data;
    }

    /**
     * Get a value via magic method.
     *
     * @param string $name
     *
     * @return null|mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        if (array_key_exists($name, $this->_mondocUnmapped)) {
            return $this->_mondocUnmapped[$name];
        }

        return null;
    }

    /**
     * Set a value via magic method.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;

            return;
        }
        $this->_mondocUnmapped[$name] = $value;
    }

    /**
     * Magic method to echo the class name if accidentally used as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }

    /**
     * Get an array export representation of this model and all child models.
     *
     * @param bool $withEncryption
     * @return array
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function asArray(bool $withEncryption = false): array
    {
        $data = [];
        foreach ($this->getMondocObjectVars() as $originalK => $v) {
            $k = $this->getFieldAliasMapRemoteName($originalK);
            if ($this->isMondocNestedAnyType([$k, $originalK]) === true) {
                if ($this->isMondocNestedMultipleObjects([$k, $originalK]) === true) {
                    /* @var $v MondocAbstractSubModel[] */
                    $subs = [];
                    foreach ($v as $datum) {
                        if ($datum instanceof MondocAbstractSubModel) {
                            $subs[] = $datum->asArray($withEncryption);
                        }
                    }
                    $v = $subs;
                } else {
                    if ($v instanceof MondocAbstractSubModel) {
                        /* @var $v MondocAbstractSubModel */
                        $v = $v->asArray($withEncryption);
                    }
                }
            } elseif ($v instanceof DateTime) {
                $v = MondocTypes::phpDateToMongoDateTime($v);
            }

            if ($withEncryption === true) {
                $data[$k] = $this->encryptMondocField(
                    $this->getFieldAliasMapLocalName(
                        $k
                    ),
                    $v
                );
            } else {
                $data[$k] = $v;
            }
        }
        if ($this->hasUnmappedProperties() === true) {
            $data = array_merge_recursive($this->_mondocUnmapped, $data);
        }

        return $data;
    }

    /**
     * Export the model as a JSON encodable array, omitting certain fields.
     *
     * @param array $omitKeys (optional) fields to omit.
     * @return array
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public function asJsonEncodableArray(array $omitKeys = []): array
    {
        $data = MondocTypes::typeToJsonFriendly(
            $this->asArray(false)
        );
        foreach ($omitKeys as $o) {
            unset($data[$o]);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getMondocObjectVars(): array
    {
        $vars = get_object_vars($this);
        $ignore = $this->getPropertyExclusions();
        foreach ($ignore as $i) {
            unset($vars[$i]);
        }

        return $vars;
    }

    /**
     * @return bool
     */
    public function isVersionableModel(): bool
    {
        return false;
    }

    /**
     * Does the model contain this revision number trait?
     *
     * @return bool
     */
    public function isRevisionNumberModel(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMondocModel(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMondocSubModel(): bool
    {
        return true;
    }
}
