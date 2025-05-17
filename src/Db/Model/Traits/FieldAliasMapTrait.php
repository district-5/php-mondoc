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
 * Trait FieldAliasMapTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait FieldAliasMapTrait
{
    /**
     * An array holding original key to new keys.
     *
     * @example
     *    [
     *        'some_underscore_key' => 'someCamelCaseKey'
     *    ]
     *
     * @var string[]
     */
    protected array $mondocFieldAliases = [];

    /**
     * Initialise the field aliases.
     * @return void
     */
    protected function initMondocFieldAliases(): void
    {
        $this->mondocFieldAliases['_id'] = '_mondocObjectId';
    }

    /**
     * @return string[]
     */
    protected function getFieldAliasMap(): array
    {
        return $this->mondocFieldAliases;
    }

    /**
     * @deprecated - See getFieldAliasMapRemoteName() and getFieldAliasMapLocalName()
     * @see FieldAliasMapTrait::getFieldAliasMapRemoteName()
     * @see FieldAliasMapTrait::getFieldAliasMapLocalName()
     */
    public function getFieldAliasSingleMap(string $field, bool $remote): string
    {
        if ($remote) {
            return $this->getFieldAliasMapRemoteName(
                $field
            );
        }

        return $this->getFieldAliasMapLocalName(
            $field
        );
    }

    /**
     * Get a field alias $mondocFieldAliases for the remote name. ie, the property name
     * in the database.
     *
     * $mondocFieldAliases = [
     *     'a_key' => 'aFriendlyVariableNameForCode'
     * ]
     *
     * Passing ('aFriendlyVariableNameForCode') would return 'a_key'
     * Similarly, passing ('a_key') would return 'a_key'
     *
     * @param string $field
     * @return string
     *
     * @see FieldAliasMapTrait::$mondocFieldAliases
     */
    public function getFieldAliasMapRemoteName(string $field): string
    {
        $fieldMap = $this->getFieldAliasMap();
        $key = array_search($field, $fieldMap);
        if ($key !== false) {
            return $key;
        }
        return $field;
    }

    /**
     * Get a field alias $mondocFieldAliases for the local name. ie, the property name
     * in the database.
     *
     * Passing ('a_key') would return 'aFriendlyVariableNameForCode'
     * Similarly, passing ('aFriendlyVariableNameForCode') would return 'aFriendlyVariableNameForCode'
     *
     * $mondocFieldAliases = [
     *     'a_key' => 'aFriendlyVariableNameForCode'
     * ]
     *
     * @param string $field
     * @return string
     *
     * @see FieldAliasMapTrait::$mondocFieldAliases
     */
    public function getFieldAliasMapLocalName(string $field): string
    {
        $fieldMap = $this->getFieldAliasMap();
        if (array_key_exists($field, $fieldMap)) {
            return $fieldMap[$field];
        }
        return $field;
    }
}
