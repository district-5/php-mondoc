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

namespace District5\Mondoc\Db\Model\Traits;

use District5\Mondoc\Db\Model\MondocAbstractSubModel;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * Trait KeyToClassMapInflationTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait KeyToClassMapInflationTrait
{
    /**
     * @param array $data
     *
     * @return $this
     */
    abstract public static function inflateSingleArray(array $data): static;

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    abstract public function __get(string $name);

    /**
     * @return array
     */
    abstract protected function getKeyToClassMap(): array;

    /**
     * Inflate any key to class map values.
     */
    protected function inflateKeyToClassMaps(): void
    {
        $nestedMap = $this->getKeyToClassMap();
        foreach ($nestedMap as $key => $classMap) {
            $this->{$key} = $this->__get($key);
            if (array_key_exists($key, $this->_mondocUnmapped)) {
                unset($this->_mondocUnmapped[$key]);
                if (property_exists($this, $key)) {
                    unset($this->{$key});
                }
            }
        }
        $nestedMap = $this->mondocNested;
        foreach ($nestedMap as $k => $className) {
            $clz = $className;
            $isSingle = true;
            if (str_ends_with($clz, '[]')) {
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
                    foreach ($data as $v) {
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
