<?php

/**
 * District5 - Mondoc
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 */

namespace District5Tests\MondocTests\Example;

use DateTime;
use District5\Mondoc\Model\MondocAbstractModel;
use MongoDB\BSON\UTCDateTime;

/**
 * Class DateModel.
 *
 * @package MyNs\Model
 */
class DateModel extends MondocAbstractModel
{
    /**
     * @var DateTime|UTCDateTime
     */
    protected $date;

    /**
     * @param DateTime $date
     *
     * @return $this
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
        $this->addDirty('date', $this->date);

        return $this;
    }

    /**
     * @param bool $asMongo
     *
     * @return DateTime|UTCDateTime
     */
    public function getDate($asMongo = false)
    {
        return $this->convertDateObject($this->date, $asMongo);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return false;
    }

    /**
     * Called to assign any default variables. You should always check for presence as
     * this method is called at both before save, and after retrieval. This avoids overwriting
     * your set values.
     */
    protected function assignDefaultVars()
    {
        // TODO: Implement
    }
}
