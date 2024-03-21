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

use District5\Mondoc\Helper\MondocTypes;
use MongoDB\BSON\ObjectId;

/**
 * Trait MondocObjectIdTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocObjectIdTrait
{
    /**
     * The ObjectId once the model has been saved for the first time.
     *
     * @var null|ObjectId
     */
    protected ?ObjectId $_mondocObjectId = null;

    /**
     * Get the string value for the ObjectId of the persisted model.
     *
     * @return null|string
     */
    public function getObjectIdString(): ?string
    {
        if ($this->hasObjectId()) {
            return $this->getObjectId()->__toString();
        }

        return null;
    }

    /**
     * Does this model have an ObjectId? IE, has it been saved before?
     *
     * @return bool
     */
    public function hasObjectId(): bool
    {
        return is_object($this->getObjectId()) && $this->getObjectId() instanceof ObjectId;
    }

    /**
     * Get the ObjectId of the persisted model.
     *
     * @return null|ObjectId
     */
    public function getObjectId(): ?ObjectId
    {
        return MondocTypes::toObjectId(
            $this->_mondocObjectId
        );
    }

    /**
     * Remove the ObjectId from this model. Ideal for cloning a document.
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function unsetObjectId()
    {
        $this->_mondocObjectId = null;

        return $this;
    }

    /**
     * Set the ObjectId for this model. Primarily used by the service.
     *
     * @param ObjectId $objectId
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    final public function setObjectId(ObjectId $objectId)
    {
        $this->_mondocObjectId = $objectId;

        return $this;
    }
}
