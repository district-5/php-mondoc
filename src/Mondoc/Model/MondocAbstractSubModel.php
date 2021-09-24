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

namespace District5\Mondoc\Model;

use DateTime;
use District5\Mondoc\Helper\MondocMongoTypeConverter;
use District5\Mondoc\Model\Traits\KeyToClassMapInflationTrait;

/**
 * Trait MondocAbstractSubModel.
 *
 * @package District5\Mondoc\Model
 */
abstract class MondocAbstractSubModel
{
    use KeyToClassMapInflationTrait;

    /**
     * An array holding data on which keys need to be coerced into sub DTOs.
     *
     * @example [
     *      'property' => '\Full\Class\Name'
     * ]
     *
     * @var string[]
     */
    protected $keyToClassMap = [];

    /**
     * An array holding all keys to values that weren't found in the object.
     *
     * @var array
     */
    protected $unmappedFields = [];

    /**
     * An array holding original key to new keys.
     *
     * @example [
     *      'some_underscore_key' => 'someCamelCaseKey'
     * ]
     *
     * @var string[]
     */
    protected $fieldToFieldMap = [];

    /**
     * MondocAbstractSubModel constructor.
     */
    public function __construct()
    {
        foreach ($this->keyToClassMap as $k => $className) {
            if ('[]' === substr($className, -2, 2)) {
                $this->{$k} = [];
            } else {
                if (class_exists($className)) {
                    $this->{$k} = new $className();
                }
            }
        }
    }

    /**
     * Set a value via magic method.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;

            return;
        }
        $this->unmappedFields[$name] = $value;
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
        if (array_key_exists($name, $this->unmappedFields)) {
            return $this->unmappedFields[$name];
        }

        return null;
    }

    /**
     * Magic method to echo the class name if accidentally used as a string.
     *
     * @return false|string
     */
    public function __toString()
    {
        return strval(get_class($this));
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
            if (array_key_exists($k, $this->getKeyToClassMap())) {
                if ('[]' === substr($this->getKeyToClassMap()[$k], -2)) {
                    /* @var $v MondocAbstractSubModel[] */
                    $subs = [];
                    foreach ($v as $datum) {
                        $subs[] = $datum->asArray();
                    }
                    $v = $subs;
                } else {
                    /* @var $v MondocAbstractSubModel */
                    $v = $v->asArray();
                }
            } elseif ($v instanceof DateTime) {
                $v = MondocMongoTypeConverter::phpDateToMongoDateTime($v);
            }
            $data[$k] = $v;
        }
        if (!empty($this->unmappedFields)) {
            $data = array_merge_recursive($this->unmappedFields, $data);
        }

        return $data;
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
     *          'name' => 'Joe',
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
        $inst = new $cl();
        /* @var $cl MondocAbstractSubModel - it's not really. */
        if (!$inst instanceof MondocAbstractSubModel) {
            return [];
        }
        $final = [];
        foreach ($data as $_ => $datum) {
            $final[] = $cl::inflateSingleArray($datum);
        }

        return $final;
    }

    /**
     * Inflate a DTO from an array of data.
     *
     * @param array $data
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection DuplicatedCode
     */
    public static function inflateSingleArray(array $data)
    {
        $cl = get_called_class();
        $inst = new $cl();
        if (!$inst instanceof MondocAbstractSubModel) {
            return null;
        }
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

            if (is_array($v) && array_key_exists($k, $classMap)) {
                $subClassName = $classMap[$k];
                $isMulti = false;
                if ('[]' === substr($subClassName, -2)) {
                    $isMulti = true;
                    $subClassName = substr($subClassName, 0, -2);
                }
                if (class_exists($subClassName)) {
                    /* @var $subClassName MondocAbstractSubModel */
                    if ($isMulti) {
                        $v = $subClassName::inflateMultipleArrays($v);
                    } else {
                        $v = $subClassName::inflateSingleArray($v);
                        if (null === $v) {
                            continue;
                        }
                    }
                } else {
                    // Class didn't exist, so adding to unmapped.
                    $inst->unmappedFields[$k] = $v;
                }
            }
            $inst->__set($k, $v);
        }

        return $inst;
    }

    /**
     * Get any unmapped fields.
     *
     * @return array
     */
    public function getUnmappedFields(): array
    {
        return $this->unmappedFields;
    }

    /**
     * @return array
     */
    public function getMondocObjectVars(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return string[]
     */
    protected function getFieldToFieldMap(): array
    {
        return $this->fieldToFieldMap;
    }

    /**
     * @return array
     */
    protected function getKeyToClassMap(): array
    {
        return $this->keyToClassMap;
    }

    /**
     * Holds an array of protected variable names.
     *
     * @return array
     */
    protected function getPropertyExclusions(): array
    {
        return [
            'keyToClassMap', 'unmappedFields', 'fieldToFieldMap', '_mongoCollection',
            '_mondocDirty', '_mondocPresetMongoId', '_mondocMongoId', '_mondocBson'
        ];
    }

    /**
     * Helper method to convert an object (with potential arrays or objects inside) into a normal array.
     *
     * @param object $obj
     *
     * @return array
     */
    protected static function objectToArray($obj): array
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
}