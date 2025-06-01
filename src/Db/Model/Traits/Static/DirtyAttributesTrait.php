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

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Helper\MondocTypes;
use MongoDB\Model\BSONDocument;

/**
 * Trait DirtyAttributesTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits\Static
 */
trait DirtyAttributesTrait
{
    /**
     * Holds any explicitly defined dirty values. As called with
     * `$this->addDirty('foo');`. Dirty values aren't referenced for
     * new objects. New documents are established by the presence of
     * an `_id` field. Which results in a full insertion.
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
     * Add a dirty value, indicating it should be saved upon updating. Dirty values aren't referenced for new objects.
     * Using this method is unnecessary if you're setting a value directly, as the value is either for a new model
     * or it's calculated as dirty. However, there may be circumstances where you want to mark a field as dirty
     * explicitly.
     *
     * @param string $property
     *
     * @return $this
     */
    protected function addDirty(string $property): static
    {
        $this->_mondocDirty[] = $property;

        return $this;
    }

    /**
     * Check if a field is dirty. Dirty values aren't referenced for new objects.
     *
     * @param string|null $property
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public function isDirty(string|null $property): bool
    {
        if ($property === null) {
            return !empty($this->getDirty());
        }

        return $this->isDirtyField($property);
    }

    /**
     * Get the array of dirty values (values that need to be updated). Dirty values aren't referenced for new objects.
     *
     * @return array
     * @throws MondocConfigConfigurationException
     */
    public function getDirty(): array
    {
        $initialDirty = $this->_mondocDirty;
        foreach ($this->getMondocObjectVars() as $key => $value) {
            if (in_array($key, $initialDirty)) {
                continue;
            }
            $aliased = $this->getFieldAliasMapRemoteName($key);
            if ($this->isDirtyField($aliased) === true) {
                $initialDirty[] = $key;
            }
        }

        $additionalVars = $this->getUnmappedFields();
        foreach ($additionalVars as $key => $value) {
            if ($this->isDirtyField($key) === true) {
                $initialDirty[] = $key;
            }
        }

        return array_unique($initialDirty);
    }

    /**
     * Check if a field is dirty. Dirty values aren't referenced for new objects.
     *
     * @param string $property
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public function isDirtyField(string $property): bool
    {
        if ($this->getOriginalBsonDocument() === null) {
            return true;
        }
        if (in_array($property, $this->_mondocDirty)) {
            return true;
        }
        $bsonValues = $this->getOriginalBsonDocument()->getArrayCopy();
        if (!array_key_exists($property, $bsonValues)) {
            return true;
        }

        return MondocTypes::typeToJsonFriendly($this->__get($property)) !== MondocTypes::typeToJsonFriendly($bsonValues[$property]);
    }

    /**
     * @see MondocAbstractModel::getOriginalBsonDocument()
     */
    abstract public function getOriginalBsonDocument(): ?BSONDocument;

    /**
     * @see MondocAbstractSubModel::getMondocObjectVars()
     */
    abstract public function getMondocObjectVars(): array;

    /**
     * @see ExcludedPropertiesTrait::getPropertyExclusions()
     */
    abstract protected function getPropertyExclusions(): array;

    /**
     * @deprecated - no longer used actively and will be removed in future versions.
     */
    abstract public function getFieldAliasSingleMap(string $field, bool $remote): string;

    /**
     * @see FieldAliasMapTrait::getFieldAliasMapRemoteName()
     */
    abstract public function getFieldAliasMapRemoteName(string $field): string;

    /**
     * @see FieldAliasMapTrait::getFieldAliasMapLocalName()
     */
    abstract public function getFieldAliasMapLocalName(string $field): string;

    /**
     * @see UnmappedPropertiesTrait::getUnmappedFields()
     */
    abstract public function getUnmappedFields(): array;
}
