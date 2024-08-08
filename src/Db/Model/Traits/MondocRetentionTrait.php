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
use MongoDB\BSON\UTCDateTime;

/**
 * Trait MondocRetentionTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits
 */
trait MondocRetentionTrait
{
    /**
     * @var DateTime|UTCDateTime|null
     */
    private DateTime|UTCDateTime|null $_mondocRetentionExpiry = null;

    /**
     * @var array
     */
    private array $_mondocRetentionChangeMeta = [];

    /**
     * @param array $data
     * @return $this
     */
    public function setMondocRetentionChangeMeta(array $data): static
    {
        $this->_mondocRetentionChangeMeta = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getMondocRetentionChangeMeta(): array
    {
        return $this->_mondocRetentionChangeMeta;
    }

    /**
     * @param DateTime|UTCDateTime|null $expiry
     * @return $this
     */
    public function setMondocRetentionExpiry(DateTime|UTCDateTime|null $expiry): static
    {
        $this->_mondocRetentionExpiry = $expiry;

        return $this;
    }

    /**
     * @return DateTime|UTCDateTime|null
     */
    public function getMondocRetentionExpiry(): DateTime|UTCDateTime|null
    {
        return $this->_mondocRetentionExpiry;
    }
}
