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

use DateTime;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use MongoDB\BSON\UTCDateTime;

/**
 * Class FinancialCandleModel.
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class FinancialCandleModel extends MondocAbstractModel
{
    /**
     * @var float|int
     */
    protected float|int $price;

    /**
     * @var DateTime|UTCDateTime
     */
    protected DateTime|UTCDateTime $date;

    /**
     * @var string|null
     */
    protected string|null $symbol;


    /**
     * @param bool $asMongo
     *
     * @return DateTime|UTCDateTime
     */
    public function getDate(bool $asMongo = false): UTCDateTime|DateTime
    {
        return $this->convertDateObject($this->date, $asMongo);
    }

    /**
     * @param DateTime $date
     *
     * @return $this
     */
    public function setDate(DateTime $date): static
    {
        $this->date = $date;
        $this->addDirty('date');

        return $this;
    }

    /**
     * @return float|int
     */
    public function getPrice(): float|int
    {
        return $this->price;
    }

    /**
     * @param float|int $price
     *
     * @return $this
     */
    public function setPrice(float|int $price): static
    {
        $this->price = $price;
        $this->addDirty('price');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSymbol(): string|null
    {
        return $this->symbol;
    }

    /**
     * @param string|null $symbol
     *
     * @return $this
     */
    public function setSymbol(string|null $symbol): static
    {
        $this->symbol = $symbol;
        $this->addDirty('symbol');

        return $this;
    }
}
