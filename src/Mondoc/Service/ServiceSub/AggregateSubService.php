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

namespace District5\Mondoc\Service\ServiceSub;

use District5\Mondoc\Service\MondocAbstractService;
use District5\Mondoc\Traits\Aggregation\AverageFieldTrait;
use District5\Mondoc\Traits\Aggregation\PercentileOfNumberFieldTrait;
use District5\Mondoc\Traits\Aggregation\SumFieldTrait;

/**
 * Class AggregateSubService.
 *
 * @package District5\Mondoc\Service\ServiceSub
 * @noinspection PhpUnused
 */
class AggregateSubService
{
    use AverageFieldTrait;
    use PercentileOfNumberFieldTrait;
    use SumFieldTrait;

    /**
     * @var MondocAbstractService|string
     */
    protected $service;

    /**
     * AggregateSubService constructor.
     *
     * @param MondocAbstractService|string $serviceClass
     */
    public function __construct($serviceClass)
    {
        $this->service = $serviceClass;
    }
}
