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
 * Trait DirtyAttributesTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait DirtyAttributesTrait
{
    /**
     * Holds any dirty values. As called with `$this->addDirty('foo');`
     * Dirty values aren't referenced for new objects. New documents
     * are established by the presence of an `_id` field. Which results
     * in a full insertion.
     *
     * @var array
     */
    protected array $_mondocDirty = [];

    /**
     * Clear the dirty parameter array. Dirty values aren't referenced for new objects.
     *
     * @return $this
     */
    public function clearDirty(): static
    {
        $this->_mondocDirty = [];

        return $this;
    }

    /**
     * Get the array of dirty values (values that need to be updated). Dirty values aren't referenced for new objects.
     *
     * @return array
     */
    public function getDirty(): array
    {
        return $this->_mondocDirty;
    }

    /**
     * Add a dirty value, indicating it should be saved upon updating. Dirty values aren't referenced for new objects.
     *
     * @param string $key
     *
     * @return $this
     */
    protected function addDirty(string $key): static
    {
        $this->_mondocDirty[] = $key;

        return $this;
    }

    /**
     * Check if a field is dirty. Dirty values aren't referenced for new objects.
     *
     * @param string|null $key
     * @return bool
     */
    public function isDirty(string|null $key): bool
    {
        if ($key === null) {
            return !empty($this->getDirty());
        }

        return in_array($key, $this->getDirty());
    }
}
