<?php

declare (strict_types=1);
namespace Jose\Experimental\Key_Encryption;

use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Encryption;
use LogicException;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
use function strlen;
final readonly class Chacha20Poly1305 implements Key_Encryption
{
    public function __construct()
    {
        if (!in_array('chacha20-poly1305', openssl_get_cipher_methods(), true)) {
            throw new LogicException('The algorithm "chacha20-poly1305" is not supported in this platform.');
        }
    }
    #[Override]
    public function allowed_key_types(): array
    {
        return ['oct'];
    }
    #[Override]
    public function name(): string
    {
        return 'chacha20-poly1305';
    }
    /**
     * @param array<string, mixed> $completeHeader
     * @param array<string, mixed> $additionalHeader
     */
    #[Override]
    public function encrypt_key(JWK $key, string $cek, array $complete_header, array &$additional_header): string
    {
        $k = $this->get_key($key);
        $nonce = random_bytes(12);
        // We set header parameters
        $additional_header['nonce'] = Base64url_Safe::encode_unpadded($nonce);
        $tag = null;
        $result = openssl_encrypt($cek, 'chacha20-poly1305', $k, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($result === false || !is_string($tag)) {
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
        isset($header['nonce']) || throw new InvalidArgumentException('The header parameter "nonce" is missing.');
        is_string($header['nonce']) || throw new InvalidArgumentException('The header parameter "nonce" is not valid.');
        $nonce = Base64url_Safe::decode_no_padding($header['nonce']);
        if (strlen($nonce) !== 12) {
            throw new InvalidArgumentException('The header parameter "nonce" is not valid.');
        }
        $result = openssl_decrypt($encrypted_cek, 'chacha20-poly1305', $k, OPENSSL_RAW_DATA, $nonce);
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