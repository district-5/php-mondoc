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

namespace District5\Mondoc\Helper;

/**
 * Class HasTraitHelper.
 *
 * @package District5\Mondoc\Helper
 */
class HasTraitHelper
{
    /**
     * @param object $object
     * @param string $trait
     * @return bool
     */
    public static function has(object $object, string $trait): bool
    {
        $traits = class_uses($object);
        if (in_array($trait, $traits)) {
            return true;
        }

        return self::parentHas(get_class($object), $trait);
    }

    /**
     * @param string $className
     * @param string $trait
     * @return bool
     */
    private static function parentHas(string $className, string $trait): bool
    {
        $parent = get_parent_class($className);
        if ($parent === false) {
            return false;
        }

        $traits = class_uses($parent);
        if (in_array($trait, $traits)) {
            return true;
        }

        return self::parentHas($parent, $trait);
    }
}
