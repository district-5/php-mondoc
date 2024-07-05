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

namespace District5Tests\MondocTests\TestObjects\Model;

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FieldAliasSubModel;

/**
 * Class MyModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class FieldAliasTestModel extends MondocAbstractModel
{
    /**
     * @var string|null
     */
    protected string|null $name = null;

    /**
     * @var string|null
     */
    protected string|null $city = null;

    /**
     * @var int
     */
    protected int $age = 0;

    /**
     * @var FieldAliasSubModel
     */
    protected FieldAliasSubModel $attributes;

    /**
     * @var FieldAliasSubModel[]
     */
    protected array $multiAttributes = [];

    protected array $mondocFieldAliases = [
        '_id' => '_mondocObjectId',
        'n' => 'name',
        'a' => 'age',
        'attr' => 'attributes'
    ];

    protected array $mondocNested = [
        'attributes' => FieldAliasSubModel::class,
        'multiAttributes' => FieldAliasSubModel::class . '[]'
    ];

    /**
     * @param FieldAliasSubModel $attributes
     *
     * @return $this
     */
    public function setAttributes(FieldAliasSubModel $attributes): static
    {
        $this->attributes = $attributes;
        $this->addDirty('attributes');
        return $this;
    }

    /**
     * @return FieldAliasSubModel
     */
    public function getAttributes(): FieldAliasSubModel
    {
        return $this->attributes;
    }

    /**
     * @param FieldAliasSubModel[] $multiAttributes
     *
     * @return $this
     */
    public function setMultiAttributes(array $multiAttributes): static
    {
        $this->multiAttributes = $multiAttributes;
        $this->addDirty('multiAttributes');
        return $this;
    }

    /**
     * @return FieldAliasSubModel[]
     */
    public function getMultiAttributes(): array
    {
        return $this->multiAttributes;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        $this->addDirty('name');
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity(string $city): static
    {
        $this->city = $city;
        $this->addDirty('city');
        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     *
     * @return $this
     */
    public function setAge(int $age): static
    {
        $this->age = $age;
        $this->addDirty('age');
        return $this;
    }
}
