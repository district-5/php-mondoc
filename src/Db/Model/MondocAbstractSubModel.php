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
use District5\Mondoc\Db\Model\Traits\UnmappedPropertiesTrait;
use District5\Mondoc\Helper\MondocTypes;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Trait MondocAbstractSubModel.
 *
 * @package District5\Mondoc\Db\Model
 */
abstract class MondocAbstractSubModel
{
    use UnmappedPropertiesTrait;

    /**
     * An array holding data on which keys need to be coerced into sub DTOs.
     *
     * @example
     *      [
     *          'property' => '\Full\Class\Name'
     *      ]
     *
     * @var string[]
     */
    protected array $mondocNested = [];

    /**
     * An array holding all established single nested objects (IE, BSONDocument not BSONArray).
     * @example [ 'theField' => <bool> ]
     *
     * @var array
     */
    protected array $_mondocEstablishedNestedSingle = [];

    /**
     * An array holding all established multiple nested objects (IE, BSONArray not BSONDocument).
     * @example [ 'theField' => <bool> ]
     *
     * @var array
     */
    protected array $_mondocEstablishedNestedMultiple = [];

    /**
     * An array holding original key to new keys.
     *
     * @example
     *      [
     *      'some_underscore_key' => 'someCamelCaseKey'
     *      ]
     *
     * @var string[]
     */
    protected array $fieldToFieldMap = [];

    /**
     * MondocAbstractSubModel constructor.
     */
    public function __construct()
    {
        foreach ($this->mondocNested as $k => $className) {
            if (str_ends_with($className, '[]')) {
                $this->{$k} = [];
                $cleanedName = substr($className, 0, -2);
                if (class_exists($cleanedName)) {
                    $this->_mondocEstablishedNestedMultiple[$k] = true;
                    $this->_mondocEstablishedNestedSingle[$k] = false;
                }
            } else if (class_exists($className)) {
                $this->{$k} = new $className();
                $this->_mondocEstablishedNestedSingle[$k] = true;
                $this->_mondocEstablishedNestedMultiple[$k] = false;
            }
        }
    }

    /**
     * Inflate an array of DTOs from an array of arrays containing data.
     *
     * @param array $data
     *
     * @return $this[]
     *
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
     */
    public static function inflateSingleArray(array $data): static
    {
        $cl = get_called_class();
        $inst = new $cl();
        $classMap = $inst->getKeyToClassMap();

        $fieldMap = $inst->getFieldToFieldMap();
        foreach ($data as $k => $v) {
            if (is_int($k)) {
                continue;
            }
            if (in_array($k, $inst->getPropertyExclusions())) {
                continue;
            }
            if (array_key_exists($k, $fieldMap)) {
                $k = $fieldMap[$k];
            }

            $isInClassMap = array_key_exists($k, $classMap);
            if ((is_array($v) || is_object($v)) && $isInClassMap === true) {
                $subClassName = $classMap[$k];
                $isMulti = false;
                if (str_ends_with($subClassName, '[]')) {
                    $isMulti = true;
                    $subClassName = substr($subClassName, 0, -2);
                }
                if (class_exists($subClassName)) {
                    if (is_object($v)) {
                        $v = MondocTypes::arrayToPhp($v);
                    }
                    /* @var $subClassName MondocAbstractSubModel */
                    if ($isMulti) {
                        $v = $subClassName::inflateMultipleArrays($v);
                    } else {
                        $v = $subClassName::inflateSingleArray($v);
                    }
                } else {
                    // Class didn't exist, so adding to unmapped.
                    $inst->_mondocUnmapped[$k] = $v;
                }
            }
            if ($isInClassMap === false && ($v instanceof BSONDocument || $v instanceof BSONArray)) {
                $v = $v->getArrayCopy();
            }

            $inst->__set($k, $v);
        }

        return $inst;
    }

    /**
     * @return array
     */
    protected function getKeyToClassMap(): array
    {
        return $this->mondocNested;
    }

    /**
     * @return string[]
     */
    protected function getFieldToFieldMap(): array
    {
        return $this->fieldToFieldMap;
    }

    /**
     * Holds an array of protected variable names.
     *
     * @return array
     */
    protected function getPropertyExclusions(): array
    {
        return [
            'mondocNested', 'fieldToFieldMap', '_mondocCollection', '_mondocUnmapped', '_mondocDirty',
            '_mondocPresetObjectId', '_mondocObjectId', '_mondocBson', '_mondocEstablishedNestedSingle',
            '_mondocEstablishedNestedMultiple'
        ];
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
     * @return array
     */
    public function asArray(): array
    {
        $data = [];
        foreach ($this->getMondocObjectVars() as $k => $v) {
            if (in_array($k, $this->getPropertyExclusions())) {
                continue;
            }
            if ($this->isMondocNestedAnyType($k) === true) {
                if ($this->isMondocNestedMultipleObjects($k) === true) {
                    /* @var $v MondocAbstractSubModel[] */
                    $subs = [];
                    foreach ($v as $datum) {
                        if ($datum instanceof MondocAbstractSubModel) {
                            $subs[] = $datum->asArray();
                        }
                    }
                    $v = $subs;
                } else {
                    if ($v instanceof MondocAbstractSubModel) {
                        /* @var $v MondocAbstractSubModel */
                        $v = $v->asArray();
                    }
                }
            } elseif ($v instanceof DateTime) {
                $v = MondocTypes::phpDateToMongoDateTime($v);
            }
            $data[$k] = $v;
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
     */
    public function asJsonEncodableArray(array $omitKeys = []): array
    {
        $data = MondocTypes::typeToJsonFriendly(
            $this->asArray()
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
        return get_object_vars($this);
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function isMondocNestedAnyType(string $field): bool
    {
        return $this->isMondocNestedSingleObject($field) === true || $this->isMondocNestedMultipleObjects($field) === true;
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function isMondocNestedSingleObject(string $field): bool
    {
        if (array_key_exists($field, $this->_mondocEstablishedNestedSingle)) {
            return $this->_mondocEstablishedNestedSingle[$field];
        }
        $this->_mondocEstablishedNestedSingle[$field] = (array_key_exists($field, $this->getKeyToClassMap()) && !str_ends_with($this->getKeyToClassMap()[$field], '[]'));

        return $this->_mondocEstablishedNestedSingle[$field];
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function isMondocNestedMultipleObjects(string $field): bool
    {
        if (array_key_exists($field, $this->_mondocEstablishedNestedMultiple)) {
            return $this->_mondocEstablishedNestedMultiple[$field];
        }
        $this->_mondocEstablishedNestedMultiple[$field] = (array_key_exists($field, $this->getKeyToClassMap()) && str_ends_with($this->getKeyToClassMap()[$field], '[]'));
        return $this->_mondocEstablishedNestedMultiple[$field];
    }

    /**
     * @return bool
     */
    public function isVersionableModel(): bool
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
