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

namespace District5\Mondoc\Db\Model\Traits;

use DateTime;
use District5\Date\Date;
use MongoDB\BSON\UTCDateTime;

/**
 * Trait MondocModifiedDateTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocModifiedDateTrait
{
    use MondocMongoTypeTrait;

    /**
     * The modified date of the model
     *
     * @var DateTime|UTCDateTime|null
     */
    protected DateTime|UTCDateTime|null $md = null;

    /**
     * Get the modified date of this model
     *
     * @param bool $asMongo
     * @return DateTime|UTCDateTime|null
     */
    public function getModifiedDate(bool $asMongo = false): DateTime|UTCDateTime|null
    {
        return $this->convertDateObject(
            $this->md,
            $asMongo
        );
    }

    /**
     * Set the modified date of this model
     *
     * @param DateTime $date
     *
     * @return $this
     */
    public function setModifiedDate(DateTime $date): static
    {
        $this->md = $date;
        if (method_exists($this, 'addDirty')) {
            $this->addDirty('md');
        }
        return $this;
    }

    /**
     * Touch the modified date
     *
     * @return $this
     */
    public function touchModifiedDate(): static
    {
        return $this->setModifiedDate(
            Date::nowUtc()
        );
    }
}
