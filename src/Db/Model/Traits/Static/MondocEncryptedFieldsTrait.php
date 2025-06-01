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

namespace District5\Mondoc\Db\Model\Traits\Static;


use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\MondocConfig;

/**
 * Trait MondocEncryptedFieldsTrait.
 *
 * @package District5\Mondoc\Db\Model\Traits\Static
 */
trait MondocEncryptedFieldsTrait
{
    /**
     * An array holding the field names to be encrypted.
     *
     * @example
     *      [
     *          'firstName',
     *          'socialSecurityNumber',
     *      ]
     *
     * @var string[]
     */
    protected array $mondocEncrypted = [];

    /**
     * Check whether the field is encrypted or not.
     *
     * @param string $field
     *
     * @return bool
     */
    public function isMondocFieldEncrypted(string $field): bool
    {
        return in_array($field, $this->mondocEncrypted, true);
    }

    /**
     * Decrypt the field value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function decryptMondocField(string $field, mixed $value): mixed
    {
        if (MondocConfig::getInstance()->hasEncryptionAdapter() === false) {
            return $value;
        }

        if (!$this->isMondocFieldEncrypted($field)) {
            return $value;
        }

        return MondocConfig::getInstance()->getEncryptionAdapter()->decrypt(
            $field,
            $value
        );
    }

    /**
     * Encrypt the field value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return string|mixed
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function encryptMondocField(string $field, mixed $value): mixed
    {
        if (MondocConfig::getInstance()->hasEncryptionAdapter() === false) {
            return $value;
        }

        if (!$this->isMondocFieldEncrypted($field)) {
            return $value;
        }

        return MondocConfig::getInstance()->getEncryptionAdapter()->encrypt(
            $field,
            $value
        );
    }
}
