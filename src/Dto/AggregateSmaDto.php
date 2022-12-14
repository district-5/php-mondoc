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
 * Class AggregateSmaDto.
 *
 * @package District5\Mondoc\Dto
 */
class AggregateSmaDto
{
    /**
     * @var float|int
     */
    private float|int $price;

    /**
     * @var DateTime
     */
    private DateTime $date;

    /**
     * @var float|int
     */
    private float|int $sma;

    /**
     * Construct the DTO by passing the necessary data.
     *
     * @param float|int $price
     * @param DateTime $dateTime
     * @param float|int $sma
     */
    public function __construct(float|int $price, float|int $sma, DateTime $dateTime)
    {
        $this->price = $price;
        $this->sma = $sma;
        $this->date = $dateTime;
    }

    /**
     * Get the price.
     *
     * @return float|int
     * @noinspection PhpUnused
     */
    public function getPrice(): float|int
    {
        return $this->price;
    }

    /**
     * Get the sma.
     *
     * @return float|int
     * @noinspection PhpUnused
     */
    public function getSma(): float|int
    {
        return $this->sma;
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
