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

/**
 * Class HookedModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class HookedModel extends MondocAbstractModel
{
    protected string $name = '';
    protected int $age = 0;

    // Private so they don't appear in asArray() serialisation
    private bool $_vetoInsert = false;
    private bool $_vetoUpdate = false;
    private bool $_vetoDelete = false;

    private int $_beforeInsertCount = 0;
    private int $_afterInsertCount = 0;
    private int $_beforeUpdateCount = 0;
    private int $_afterUpdateCount = 0;
    private int $_beforeDeleteCount = 0;
    private int $_afterDeleteCount = 0;

    public function setVetoInsert(bool $v): void { $this->_vetoInsert = $v; }
    public function setVetoUpdate(bool $v): void { $this->_vetoUpdate = $v; }
    public function setVetoDelete(bool $v): void { $this->_vetoDelete = $v; }

    public function getBeforeInsertCount(): int { return $this->_beforeInsertCount; }
    public function getAfterInsertCount(): int { return $this->_afterInsertCount; }
    public function getBeforeUpdateCount(): int { return $this->_beforeUpdateCount; }
    public function getAfterUpdateCount(): int { return $this->_afterUpdateCount; }
    public function getBeforeDeleteCount(): int { return $this->_beforeDeleteCount; }
    public function getAfterDeleteCount(): int { return $this->_afterDeleteCount; }

    public function beforeInsert(): bool
    {
        $this->_beforeInsertCount++;
        return !$this->_vetoInsert;
    }

    public function afterInsert(): void
    {
        $this->_afterInsertCount++;
    }

    public function beforeUpdate(): bool
    {
        $this->_beforeUpdateCount++;
        return !$this->_vetoUpdate;
    }

    public function afterUpdate(): void
    {
        $this->_afterUpdateCount++;
    }

    public function beforeDelete(): bool
    {
        $this->_beforeDeleteCount++;
        return !$this->_vetoDelete;
    }

    public function afterDelete(): void
    {
        $this->_afterDeleteCount++;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getAge(): int { return $this->age; }
    public function setAge(int $age): static { $this->age = $age; return $this; }

    protected function assignDefaultVars(): void
    {
    }
}
