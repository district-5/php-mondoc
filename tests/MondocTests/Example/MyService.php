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

use District5\Mondoc\Service\MondocAbstractService;

/**
 * Class MyService.
 *
 * @package District5Tests\MondocTests\Service
 */
class MyService extends MondocAbstractService
{
    /**
     * @var string
     */
    protected static string $modelClassName = MyModel::class;

    /**
     * @return string
     */
    protected static function getCollectionName(): string
    {
        return 'test_model';
    }
}
