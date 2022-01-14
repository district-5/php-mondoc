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

namespace District5Tests\MondocTests\Example\Subs;

use District5\Mondoc\Model\MondocAbstractSubModel;

/**
 * Class FoodSubModel.
 *
 * @package District5Tests\MondocTests\Example\Subs
 */
class FoodSubModel extends MondocAbstractSubModel
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var FoodAttributesSubModel[]
     */
    protected $attributes = [];

    protected array $keyToClassMap = [
        'attributes' => FoodAttributesSubModel::class . '[]',
    ];

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setFood(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getFood(): string
    {
        return $this->type;
    }

    /**
     * @param FoodAttributesSubModel[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return FoodAttributesSubModel[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
