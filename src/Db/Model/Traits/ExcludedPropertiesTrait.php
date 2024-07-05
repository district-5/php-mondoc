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
 * Trait ExcludedPropertiesTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait ExcludedPropertiesTrait
{
    /**
     * Holds an array of protected variable names.
     *
     * @return array
     */
    protected function getPropertyExclusions(): array
    {
        return [
            'mondocNested', 'mondocFieldAliases', '_mondocCollection', '_mondocUnmapped', '_mondocDirty',
            '_mondocPresetObjectId', '_mondocObjectId', '_mondocBson', '_mondocEstablishedNestedSingle',
            '_mondocEstablishedNestedMultiple'
        ];
    }

    /**
     * Check if a field is excluded from the model.
     *
     * @param string|string[] $nameOrNames
     * @return bool
     */
    protected function isPropertyExcluded(string|array $nameOrNames): bool
    {
        $exclusions = $this->getPropertyExclusions();
        if (is_array($nameOrNames)) {
            foreach ($nameOrNames as $name) {
                if (in_array($name, $exclusions)) {
                    return true;
                }
            }
            return false;
        }
        return in_array($nameOrNames, $exclusions);
    }
}
