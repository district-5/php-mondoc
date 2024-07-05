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
 * Trait MondocRevisionNumberTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocRevisionNumberTrait
{
    /**
     * The revision number of the model
     *
     * @var int
     */
    protected int $_rn = 0;

    /**
     * Get the revision number of this model
     *
     * @return int
     */
    public function getRevisionNumber(): int
    {
        return $this->_rn;
    }

    /**
     * Set the revision number of this model
     *
     * @param int $revision
     *
     * @return $this
     */
    public function setRevisionNumber(int $revision): static
    {
        $this->_rn = $revision;
        if (method_exists($this, 'addDirty')) {
            $this->addDirty('_rn');
        }
        return $this;
    }

    /**
     * Increment the revision number of this model. When using this trait,
     * the revision number will automatically increment when saving the model.
     *
     * @return $this
     */
    public function incrementRevisionNumber(): static
    {
        if ($this->_rn === PHP_INT_MAX) {
            $this->_rn = 0;
        }
        $this->_rn++;
        if (method_exists($this, 'addDirty')) {
            $this->addDirty('_rn');
        }
        return $this;
    }

    /**
     * Does the model contain this revision number trait?
     *
     * @return bool
     */
    public function isRevisionNumberModel(): bool
    {
        return true;
    }
}
