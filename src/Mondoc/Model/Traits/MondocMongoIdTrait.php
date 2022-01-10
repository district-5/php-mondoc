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

use District5\Mondoc\Helper\MondocMongoTypeConverter;
use MongoDB\BSON\ObjectId;

/**
 * Trait MondocMongoIdTrait.
 *
 * @package District5\Mondoc\Model\Traits
 */
trait MondocMongoIdTrait
{
    /**
     * The ObjectId once the model has been saved for the first time.
     *
     * @var null|ObjectId
     */
    protected ?ObjectId $_mondocMongoId = null;

    /**
     * Get the string value for the ObjectId of the persisted model.
     *
     * @return null|string
     * @noinspection PhpUnused
     */
    public function getMongoIdString(): ?string
    {
        if ($this->hasMongoId()) {
            return $this->getMongoId()->__toString();
        }

        return null;
    }

    /**
     * Does this model have an ObjectId? IE, has it been saved before?
     *
     * @return bool
     */
    public function hasMongoId(): bool
    {
        return is_object($this->getMongoId()) && $this->getMongoId() instanceof ObjectId;
    }

    /**
     * Get the ObjectId of the persisted model.
     *
     * @return null|ObjectId
     */
    public function getMongoId(): ?ObjectId
    {
        return MondocMongoTypeConverter::convertToMongoId(
            $this->_mondocMongoId
        );
    }

    /**
     * Remove the ObjectId from this model. Ideal for cloning a document.
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     */
    public function unsetMongoId()
    {
        $this->_mondocMongoId = null;

        return $this;
    }
}
