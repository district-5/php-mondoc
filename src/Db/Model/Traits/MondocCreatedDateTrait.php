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
 *
 * @noinspection PhpUnused
 */

namespace District5\Mondoc\Db\Model\Traits;

use DateTime;
use District5\Date\Date;
use MongoDB\BSON\UTCDateTime;

/**
 * Trait MondocCreatedDateTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocCreatedDateTrait
{
    use MondocMongoTypeTrait;

    /**
     * The created date of the model
     *
     * @var DateTime|UTCDateTime|null
     */
    protected DateTime|UTCDateTime|null $cd = null;

    /**
     * Get the created date of this model
     *
     * @param bool $asMongo
     * @return DateTime|UTCDateTime|null
     */
    public function getCreatedDate(bool $asMongo = false): DateTime|UTCDateTime|null
    {
        return $this->convertDateObject(
            $this->cd,
            $asMongo
        );
    }

    /**
     * Set the created date of this model
     *
     * @param DateTime $date
     *
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setCreatedDate(DateTime $date)
    {
        $this->cd = $date;
        if (method_exists($this, 'addDirty')) {
            $this->addDirty('cd');
        }
        return $this;
    }

    /**
     * Touch the created date
     *
     * @return $this
     */
    public function touchCreatedDate(): self
    {
        return $this->setCreatedDate(
            Date::nowUtc()
        );
    }
}
