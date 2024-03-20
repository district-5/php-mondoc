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

use MongoDB\BSON\ObjectId;

/**
 * Trait PresetMongoIdTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait PresetMongoIdTrait
{
    /**
     * Assign a preset ID to be used for insertion.
     *
     * @var null|ObjectId
     */
    protected ?ObjectId $_mondocPresetMongoId = null;

    /**
     * Set the preset Mongo ID (which is used for setting the _id on insertion).
     *
     * @param null|ObjectId $presetMongoId
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     */
    public function setPresetObjectId(ObjectId|null $presetMongoId)
    {
        $this->_mondocPresetMongoId = $presetMongoId;

        return $this;
    }

    /**
     * Does this model have a preset Mongo ID (which is used for setting the _id on insertion).
     *
     * @return bool
     */
    public function hasPresetObjectId(): bool
    {
        return null !== $this->getPresetObjectId();
    }

    /**
     * @noinspection PhpMissingReturnTypeInspection
     * @deprecated Use hasPresetObjectId() instead.
     */
    public function hasPresetMongoId(): bool
    {
        return $this->hasPresetObjectId();
    }

    /**
     * Get the preset Mongo ID (which is used for setting the _id on insertion).
     *
     * @return null|ObjectId
     */
    public function getPresetObjectId(): ?ObjectId
    {
        return $this->_mondocPresetMongoId;
    }

    /**
     * @noinspection PhpMissingReturnTypeInspection
     * @deprecated Use getPresetObjectId() instead.
     */
    public function getPresetMongoId(): ?ObjectId
    {
        return $this->getPresetObjectId();
    }

    /**
     * Remove the preset Mongo ID (which is used for setting the _id on insertion).
     */
    public function clearPresetObjectId(): void
    {
        $this->_mondocPresetMongoId = null;
    }

    /**
     * @deprecated Use clearPresetObjectId() instead.
     */
    public function clearPresetMongoId(): void
    {
        $this->clearPresetObjectId();
    }
}
