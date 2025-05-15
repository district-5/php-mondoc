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

namespace District5\Mondoc\Extensions\FieldEncryption;

use District5\Mondoc\Exception\MondocEncryptionException;

/**
 * Class MondocAES256Adapter.
 *
 * @package District5\Mondoc\Extensions\FieldEncryption
 */
class MondocAES256Adapter implements MondocEncryptionInterface
{
    /**
     * @var string|null
     */
    protected string|null $encryptionKey = null;

    /**
     * MondocAES256Adapter constructor.
     *
     * @param string|null $encryptionKey
     */
    public function __construct(string|null $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Decrypt the field value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     * @throws MondocEncryptionException
     */
    public function decrypt(string $field, mixed $value): mixed
    {
        $decryptionKey = $this->encryptionKey;
        $decodedValue = base64_decode($value, true);
        if ($decodedValue === false) {
            throw new MondocEncryptionException(
                'Failed to decode the encrypted value.'
            );
        }

        $decryptedValue = openssl_decrypt(
            $decodedValue,
            'AES-256-CBC',
            $decryptionKey,
            OPENSSL_RAW_DATA,
            substr($decryptionKey, 0, 16) // Use the first 16 bytes of the key as the IV
        );

        if (openssl_error_string() !== false) {
            throw new MondocEncryptionException(
                'Failed to decrypt the value: ' . openssl_error_string()
            );
        }

        return unserialize($decryptedValue);
    }

    /**
     * Encrypt the field value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return string
     * @throws MondocEncryptionException
     */
    public function encrypt(string $field, mixed $value): string
    {
        $value = serialize($value);

        $encryptionKey = $this->encryptionKey;

        $encryptedValue = openssl_encrypt(
            $value,
            'AES-256-CBC',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            substr($encryptionKey, 0, 16) // Use the first 16 bytes of the key as the IV
        );

        return openssl_error_string() === false ? base64_encode($encryptedValue) : throw new MondocEncryptionException(
            'Failed to decrypt the value: ' . openssl_error_string()
        );
    }
}
