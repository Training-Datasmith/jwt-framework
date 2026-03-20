<?php

declare (strict_types=1);
namespace Jose\Experimental\Key_Encryption;

use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Encryption;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
abstract readonly class AESCTR implements Key_Encryption
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['oct'];
    }
    /**
     * @param array<string, mixed> $completeHeader
     * @param array<string, mixed> $additionalHeader
     */
    #[Override]
    public function encrypt_key(JWK $key, string $cek, array $complete_header, array &$additional_header): string
    {
        $k = $this->get_key($key);
        $iv = random_bytes(16);
        // We set header parameters
        $additional_header['iv'] = Base64url_Safe::encode_unpadded($iv);
        $result = openssl_encrypt($cek, $this->get_mode(), $k, OPENSSL_RAW_DATA, $iv);
        if ($result === false) {
            throw new RuntimeException('Unable to encrypt the CEK');
        }
        return $result;
    }
    /**
     * @param array<string, mixed> $header
     */
    #[Override]
    public function decrypt_key(JWK $key, string $encrypted_cek, array $header): string
    {
        $k = $this->get_key($key);
        isset($header['iv']) || throw new InvalidArgumentException('The header parameter "iv" is missing.');
        is_string($header['iv']) || throw new InvalidArgumentException('The header parameter "iv" is not valid.');
        $iv = Base64url_Safe::decode_no_padding($header['iv']);
        $result = openssl_decrypt($encrypted_cek, $this->get_mode(), $k, OPENSSL_RAW_DATA, $iv);
        if ($result === false) {
            throw new RuntimeException('Unable to decrypt the CEK');
        }
        return $result;
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_ENCRYPT;
    }
    abstract protected function get_mode(): string;
    private function get_key(JWK $key): string
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
        if (!$key->has('k')) {
            throw new InvalidArgumentException('The key parameter "k" is missing.');
        }
        $k = $key->get('k');
        if (!is_string($k)) {
            throw new InvalidArgumentException('The key parameter "k" is invalid.');
        }
        return Base64url_Safe::decode_no_padding($k);
    }
}