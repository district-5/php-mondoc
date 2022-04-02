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

namespace District5\Mondoc\Dto;

use DateTime;

/**
 * Class AggregateFinancialCandleDto.
 *
 * @package District5\Mondoc\Dto
 */
class AggregateFinancialCandleDto
{
    /**
     * @var float
     */
    private float $high;

    /**
     * @var float
     */
    private float $low;

    /**
     * @var float
     */
    private float $open;

    /**
     * @var float
     */
    private float $close;

    /**
     * @var DateTime
     */
    private DateTime $date;

    /**
     * Construct the DTO by passing the necessary data.
     *
     * @param float $high
     * @param float $low
     * @param float $open
     * @param float $close
     * @param DateTime $date
     */
    public function __construct(float $high, float $low, float $open, float $close, DateTime $date)
    {
        $this->high = $high;
        $this->low = $low;
        $this->open = $open;
        $this->close = $close;
        $this->date = $date;
    }

    /**
     * Get the high value.
     *
     * @return float
     */
    public function getHigh(): float
    {
        return $this->high;
    }

    /**
     * Get the low value.
     *
     * @return float
     */
    public function getLow(): float
    {
        return $this->low;
    }

    /**
     * Get the open value.
     *
     * @return float
     */
    public function getOpen(): float
    {
        return $this->open;
    }

    /**
     * Get the close value.
     *
     * @return float
     */
    public function getClose(): float
    {
        return $this->close;
    }

    /**
     * Get the date.
     *
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }
}
