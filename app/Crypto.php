<?php
declare(strict_types=1);

namespace App;

class Crypto
{
    /**
     * Encrypt a message
     *
     * @param string $message - message to encrypt
     * @param string $key - encryption key
     * @return string
     * @throws RangeException
     */
    public static function encrypt(string $message, string $key): string
    {
        if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RangeException('Key is not the correct size (must be 32 bytes).');
        }
        // Generate a MAC - 24 bytes
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $cipher = base64_encode(
            $nonce.
            sodium_crypto_secretbox(
                $message,
                $nonce,
                $key
            )
        );
        sodium_memzero($message);
        sodium_memzero($key);
        return $cipher;
    }

    /**
     * Decrypt a message
     *
     * @param string $encrypted - message encrypted with encrypt()
     * @param string $key - encryption key
     * @return string
     * @throws Exception
     */
    public static function decrypt(string $encrypted, string $key): string
    {
        $decoded = base64_decode($encrypted);
        // Extract nonce from first 24 bytes
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        // Use remaining bytes as ciphertext
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        // Decrypt
        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );

        // Verify decryption with extracted nonce
        if (!is_string($plain)) {
            throw new \Exception('Invalid MAC');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);
        return $plain;
    }

    /**
     * Get secret key
     *
     * @param int $bytes
     * @return string
     */
    public static function getSecretKey(int $bytes = SODIUM_CRYPTO_SECRETBOX_KEYBYTES): string
    {
        return random_bytes($bytes);
    }
}
