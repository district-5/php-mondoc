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
use District5Tests\MondocTests\TestObjects\FlexibleControlTestConfigSingleton;
use District5Tests\MondocTests\TestObjects\Model\Subs\AgeSubModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\FoodSubModel;

/**
 * Class FlexibleNestedTestModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class FlexibleNestedTestModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var AgeSubModel|FoodSubModel
     */
    protected AgeSubModel|FoodSubModel $nested;

    /**
     * @var string[]
     */
    protected array $mondocNested = [
        'nested' => FlexibleNestedTestModel::class
    ];

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

        return $this;
    }

    /**
     * @return AgeSubModel|FoodSubModel
     */
    public function getNested(): AgeSubModel|FoodSubModel
    {
        return $this->nested;
    }

    /**
     * @param AgeSubModel|FoodSubModel $val
     *
     * @return $this
     */
    public function setNested(AgeSubModel|FoodSubModel $val): static
    {
        $this->nested = $val;

        return $this;
    }

    protected function getMondocNestedModelMap(): array
    {
        return [
            'nested' => FlexibleControlTestConfigSingleton::getInstance()->getClassName() // only exists for testing purposes. In a real world scenario, you could use getenv('nested_model_class_name') or similar
        ];
    }
}
