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

namespace District5\Mondoc\Db\Model\Traits\Static;

use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\MondocConfig;
use MongoDB\BSON\ObjectId;

/**
 * Trait MondocAtomicOperationsTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits\Static
 */
trait MondocAtomicOperationsTrait
{
    /**
     * Decrement a field by a given delta.
     * Internally this method uses `inc` with a negative representation of `$delta`
     *
     * @param string $field
     * @param int $delta
     * @return bool
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function dec(string $field, int $delta = 1): bool
    {
        return $this->inc($field, ($delta - ($delta * 2)));
    }

    /**
     * Increment a field by a given delta.
     * Can also handle negative numbers.
     *
     * @param string $field
     * @param int $delta
     * @return bool
     * @throws MondocServiceMapErrorException
     * @throws MondocConfigConfigurationException
     */
    public function inc(string $field, int $delta = 1): bool
    {
        $service = MondocConfig::getInstance()->getServiceForModel(get_called_class());
        /* @var $service MondocAbstractService */
        if ($this->hasObjectId() === false) {
            $this->{$field} += $delta;
            return false;
        }

        $perform = $service::inc($this->getObjectId(), $field, $delta);
        if ($perform === true) {
            $this->{$field} += $delta;
        }

        return $perform;
    }

    /**
     * Does this model have an ObjectId? IE, has it been saved before?
     *
     * @return bool
     */
    abstract public function hasObjectId(): bool;

    /**
     * Get the ObjectId of the persisted model.
     *
     * @return null|ObjectId
     */
    abstract public function getObjectId(): ?ObjectId;
}
