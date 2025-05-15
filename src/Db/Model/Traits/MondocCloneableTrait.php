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

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\MondocAbstractSubModel;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocEncryptionException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;

/**
 * Trait MondocCloneableTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocCloneableTrait
{
    /**
     * @param bool $save
     * @param string|MondocAbstractModel|null $clz
     * @return MondocAbstractModel|static|null
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocEncryptionException
     * @noinspection PhpRedundantOptionalArgumentInspection
     */
    public function clone(bool $save = false, string|MondocAbstractModel|null $clz = null): MondocAbstractModel|static|null
    {
        if ($clz !== null) {
            /* @var $clz MondocAbstractModel */
            if ($clz instanceof MondocAbstractSubModel) {
                $clz = get_class($clz);
            }
            $new = $clz::inflateSingleArray($this->asArray(false));
        } else {
            $new = clone $this;
        }

        $new->unsetObjectId();
        $new->clearPresetObjectId();
        $new->clearDirty();
        if ($save === true) {
            return $new->save() === true ? $new : null;

        }
        return $new;
    }

    /**
     * @param bool $withEncryption
     * @return array
     */
    abstract public function asArray(bool $withEncryption = false): array;
}
