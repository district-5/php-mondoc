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

/**
 * Trait NestedModelTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait NestedModelTrait
{
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
     * Initialise the nested models.
     * @return void
     */
    protected function initMondocNestedModel(): void
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
     * @param string|string[] $fieldOrFields
     * @return bool
     */
    protected function isMondocNestedAnyType(string|array $fieldOrFields): bool
    {
        return $this->isMondocNestedSingleObject($fieldOrFields) === true || $this->isMondocNestedMultipleObjects($fieldOrFields) === true;
    }

    /**
     * @param string|string[] $fieldOrFields
     * @return bool
     */
    protected function isMondocNestedSingleObject(string|array $fieldOrFields): bool
    {
        if (is_string($fieldOrFields)) {
            if (array_key_exists($fieldOrFields, $this->_mondocEstablishedNestedSingle)) {
                return $this->_mondocEstablishedNestedSingle[$fieldOrFields];
            }
            $this->_mondocEstablishedNestedSingle[$fieldOrFields] = (array_key_exists($fieldOrFields, $this->getKeyToClassMap()) && !str_ends_with($this->getKeyToClassMap()[$fieldOrFields], '[]'));

            return $this->_mondocEstablishedNestedSingle[$fieldOrFields];
        }
        foreach ($fieldOrFields as $field) {
            if ($this->isMondocNestedSingleObject($field) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|string[] $fieldOrFields
     * @return bool
     */
    protected function isMondocNestedMultipleObjects(string|array $fieldOrFields): bool
    {
        if (is_string($fieldOrFields)) {
            if (array_key_exists($fieldOrFields, $this->_mondocEstablishedNestedMultiple)) {
                return $this->_mondocEstablishedNestedMultiple[$fieldOrFields];
            }
            $this->_mondocEstablishedNestedMultiple[$fieldOrFields] = (array_key_exists($fieldOrFields, $this->getKeyToClassMap()) && str_ends_with($this->getKeyToClassMap()[$fieldOrFields], '[]'));
            return $this->_mondocEstablishedNestedMultiple[$fieldOrFields];
        }

        foreach ($fieldOrFields as $field) {
            if ($this->isMondocNestedMultipleObjects($field) === true) {
                return true;
            }
        }

        return false;
    }
}
