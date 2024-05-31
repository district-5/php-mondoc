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
 * Trait MondocVersionedModelTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocVersionedModelTrait
{
    /**
     * The version of the model. Defaults to 1.
     *
     * @var int
     */
    protected int $_v = 1;

    /**
     * Get the version of this model
     *
     * @return int
     */
    public function getModelVersion(): int
    {
        return $this->_v;
    }

    /**
     * Is this model version $x?
     *
     * @param int $x
     * @return bool
     */
    public function isModelVersionX(int $x): bool
    {
        return $this->_v === $x;
    }

    /**
     * Set the version of this model
     *
     * @param int $version
     *
     * @return $this
     */
    public function setModelVersion(int $version): static
    {
        $this->_v = $version;
        if (method_exists($this, 'addDirty')) {
            $this->addDirty('_v');
        }
        return $this;
    }

    /**
     * Increment the version of this model
     *
     * @return $this
     */
    public function incrementModelVersion(): static
    {
        return $this->setModelVersion(
            ($this->_v + 1)
        );
    }

    /**
     * Decrement the version of this model
     *
     * @return $this
     */
    public function decrementModelVersion(): static
    {
        return $this->setModelVersion(
            ($this->_v - 1)
        );
    }

    /**
     * @return bool
     */
    public function isVersionableModel(): bool
    {
        return true;
    }
}
