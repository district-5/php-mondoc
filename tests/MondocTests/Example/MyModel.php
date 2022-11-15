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

namespace District5Tests\MondocTests\Example;

use District5\Mondoc\DbModel\MondocAbstractModel;

/**
 * Class MyModel.
 *
 * @package MyNs\Model
 */
class MyModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $age = 0;

    /**
     * @return bool
     */
    public function incrementAge(): bool
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->inc('age', 1);
    }

    /**
     * @return bool
     */
    public function decrementAge(): bool
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->dec('age', 1);
    }

    /**
     * This method must return the array to insert into Mongo.
     *
     * @return array
     */
    public function asArray(): array
    {
        $this->assignDefaultVars();

        return [
            'name' => $this->getName(),
            'age' => $this->getAge()
        ];
    }

    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars(): void
    {
        // TODO: Implement
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return trim($this->name);
    }

    /**
     * @param string $val
     *
     * @return $this
     */
    public function setName(string $val): static
    {
        $this->name = trim($val);
        $this->addDirty('name');

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
