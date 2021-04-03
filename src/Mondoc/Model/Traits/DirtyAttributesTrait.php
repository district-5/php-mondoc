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

/**
 * Trait DirtyAttributesTrait.
 *
 * @package District5\Mondoc\Model\Traits
 */
trait DirtyAttributesTrait
{
    /**
     * Holds any dirty values. As called with `$this->addDirty('foo', 'bar');` Dirty values aren't referenced for new
     * objects.
     *
     * @var array
     */
    protected $_mondocDirty = [];

    /**
     * Clear the dirty parameter array. Dirty values aren't referenced for new objects.
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function clearDirty()
    {
        $this->_mondocDirty = [];

        return $this;
    }

    /**
     * Get the array of dirty values (values that need to be updated). Dirty values aren't referenced for new objects.
     *
     * @return array
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getDirty()
    {
        return $this->_mondocDirty;
    }

    /**
     * Add a dirty value, indicating it should be saved upon updating. Dirty values aren't referenced for new objects.
     *
     * @param string $key
     * @param null   $value - ignored. @deprecated
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnusedParameterInspection
     */
    protected function addDirty(string $key, $value = null)
    {
        $this->_mondocDirty[] = $key;

        return $this;
    }
}
