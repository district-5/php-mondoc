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

namespace District5\Mondoc\Model\Traits;

use District5\Mondoc\Model\MondocAbstractSubModel;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Trait KeyToClassMapInflationTrait.
 *
 * @package District5\Mondoc\Model\Traits
 */
trait KeyToClassMapInflationTrait
{
    /**
     * @param array $data
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     */
    abstract public static function inflateSingleArray(array $data);

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    abstract public function __get(string $name);

    /**
     * @return array
     * @noinspection PhpUnused
     */
    abstract protected function getKeyToClassMap(): array;

    /**
     * Inflate any key to class map values.
     */
    protected function inflateKeyToClassMaps()
    {
        $keyToClassMap = $this->getKeyToClassMap();
        foreach ($keyToClassMap as $key => $classMap) {
            $this->{$key} = $this->__get($key);
            if (array_key_exists($key, $this->unmappedFields)) {
                unset($this->unmappedFields[$key]);
                if (property_exists($this, $key)) {
                    unset($this->{$key});
                }
            }
        }
        $keyToClassMap = $this->keyToClassMap;
        foreach ($keyToClassMap as $k => $className) {
            $clz = $className;
            $isSingle = true;
            if ('[]' === substr($clz, -2)) {
                $isSingle = false;
                $clz = substr($clz, 0, -2);
            }
            /* @var $clz MondocAbstractSubModel */
            $data = $this->__get($k);
            if (true === $isSingle) {
                if ($data instanceof BSONDocument) {
                    $data = $data->getArrayCopy();
                    $anM = $clz::inflateSingleArray($data);
                    $anM->inflateKeyToClassMaps();
                    $this->{$k} = $anM;
                } else {
                    $this->{$k} = $data;
                }
            } else {
                if ($data instanceof BSONArray) {
                    $data = $data->getArrayCopy();
                    $final = [];
                    /** @noinspection PhpUnusedLocalVariableInspection */
                    foreach ($data as $_ => $v) {
                        if ($v instanceof BSONDocument || $v instanceof BSONArray) {
                            $v = $v->getArrayCopy();
                        }
                        $anM = $clz::inflateSingleArray($v);
                        $anM->inflateKeyToClassMaps();
                        $final[] = $anM;
                    }
                    $this->{$k} = $final;
                } else {
                    $this->{$k} = $data;
                }
            }
        }
    }
}
