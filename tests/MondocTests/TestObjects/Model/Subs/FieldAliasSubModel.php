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

namespace District5Tests\MondocTests\TestObjects\Model\Subs;

use District5\Mondoc\Db\Model\MondocAbstractSubModel;
use District5\Mondoc\Db\Model\Traits\Static\FieldAliasMapTrait;

/**
 * Class FoodSubModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model\Subs
 */
class FieldAliasSubModel extends MondocAbstractSubModel
{
    /**
     * @var string
     */
    protected string $hairColor;

    /**
     * @var string
     */
    protected string $hairDescription;

    /**
     * @var string
     */
    protected string $hairLength;

    /**
     * @var array
     * @see FieldAliasMapTrait::$mondocFieldAliases
     */
    protected array $mondocFieldAliases = [
        'hc' => 'hairColor',
        'hd' => 'hairDescription',
    ];

    /**
     * @param string $hairColor
     *
     * @return $this
     */
    public function setHairColor(string $hairColor): self
    {
        $this->hairColor = $hairColor;
        return $this;
    }

    /**
     * @return string
     */
    public function getHairColor(): string
    {
        return $this->hairColor;
    }

    /**
     * @param string $hairDescription
     *
     * @return $this
     */
    public function setHairDescription(string $hairDescription): self
    {
        $this->hairDescription = $hairDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getHairDescription(): string
    {
        return $this->hairDescription;
    }

    /**
     * @param string $hairLength
     *
     * @return $this
     */
    public function setHairLength(string $hairLength): self
    {
        $this->hairLength = $hairLength;
        return $this;
    }

    /**
     * @return string
     */
    public function getHairLength(): string
    {
        return $this->hairLength;
    }
}
