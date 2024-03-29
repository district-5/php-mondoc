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

namespace District5\Mondoc\Db\Service\ServiceSub;

use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Db\Service\Traits\Aggregation\AverageFieldTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\FinancialCandlesTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\FinancialSmaTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\MaxFieldTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\MinFieldTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\PercentileOfNumberFieldTrait;
use District5\Mondoc\Db\Service\Traits\Aggregation\SumFieldTrait;

/**
 * Class AggregateSubService.
 *
 * @package District5\Mondoc\Db\Service\ServiceSub
 */
class AggregateSubService
{
    use AverageFieldTrait;
    use PercentileOfNumberFieldTrait;
    use SumFieldTrait;
    use MinFieldTrait;
    use MaxFieldTrait;
    use FinancialCandlesTrait;
    use FinancialSmaTrait;

    /**
     * @var MondocAbstractService|string
     */
    protected string|MondocAbstractService $service;

    /**
     * AggregateSubService constructor.
     *
     * @param string|MondocAbstractService $serviceClass
     */
    public function __construct(MondocAbstractService|string $serviceClass)
    {
        $this->service = $serviceClass;
    }
}
