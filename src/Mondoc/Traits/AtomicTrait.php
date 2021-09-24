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

namespace District5\Mondoc\Traits;

use District5\Mondoc\Traits\Atomic\DecrementTrait;
use District5\Mondoc\Traits\Atomic\IncrementTrait;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\UpdateResult;

/**
 * Trait AtomicTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait AtomicTrait
{
    use IncrementTrait;
    use DecrementTrait;

    /**
     * Perform an atomic operation.
     *
     * @param ObjectId $id
     * @param array    $query
     *
     * @return bool
     */
    protected static function atomic(ObjectId $id, array $query): bool
    {
        return self::updateOne(
            ['_id' => $id],
            $query
        );
    }
}
