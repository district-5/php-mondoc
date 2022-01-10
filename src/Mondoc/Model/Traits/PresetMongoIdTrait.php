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

use MongoDB\BSON\ObjectId;

/**
 * Trait PresetMongoIdTrait.
 *
 * @package District5\Mondoc\Model\Traits
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
    public function setPresetMongoId(ObjectId $presetMongoId)
    {
        $this->_mondocPresetMongoId = $presetMongoId;

        return $this;
    }

    /**
     * Does this model have a preset Mongo ID (which is used for setting the _id on insertion).
     *
     * @return bool
     */
    public function hasPresetMongoId(): bool
    {
        return null !== $this->getPresetMongoId();
    }

    /**
     * Get the preset Mongo ID (which is used for setting the _id on insertion).
     *
     * @return null|ObjectId
     */
    public function getPresetMongoId(): ?ObjectId
    {
        return $this->_mondocPresetMongoId;
    }

    /**
     * Remove the preset Mongo ID (which is used for setting the _id on insertion).
     */
    public function clearPresetMongoId()
    {
        $this->_mondocPresetMongoId = null;
    }
}
