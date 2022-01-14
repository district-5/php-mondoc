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

namespace District5\Mondoc\Traits;

use District5\Mondoc\Model\MondocAbstractModel;
use District5\Mondoc\Service\MondocAbstractService;
use District5\MondocBuilder\QueryBuilder;
use MongoDB\Model\BSONDocument;

/**
 * Trait ExistenceTrait.
 *
 * @package District5\Mondoc\Traits
 */
trait ExistenceTrait
{
    /**
     * Does a document exist? This is likely not a method you need to use, although if you're simply checking
     * for existence, you can call it.
     *
     * @param array $criteria
     * @param array $options
     * @return bool
     */
    public static function exists(array $criteria, array $options = []): bool
    {
        if (!array_key_exists('projection', $options)) {
            $options['projection'] = ['_id' => 1];
        }
        if (array_key_exists('sort', $options)) {
            $opts = array_merge($options, ['limit' => 1]);
            $results = self::getMultiByCriteria($criteria, $opts);
            return count($results) === 1;
        }

        if (array_key_exists('limit', $options)) {
            unset($options['limit']);
        }
        $collection = self::getCollection(
            get_called_class()
        );
        $match = $collection->findOne($criteria, $options);
        if ($match) {
            return true;
        }
        return false;
    }

    /**
     * Does a document exist? This is likely not a method you need to use, although if you're simply checking
     * for existence, you can call it.
     *
     * @param QueryBuilder $builder
     * @return bool
     */
    public static function existsWithQueryBuilder(QueryBuilder $builder): bool
    {
        return self::exists(
            $builder->getArrayCopy(),
            $builder->getOptions()->getArrayCopy()
        );
    }
}
