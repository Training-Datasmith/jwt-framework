<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\Wrapper as WrapperInterface;
use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
use function sprintf;
abstract readonly class AESGCMKW implements Key_Wrapping
{
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        if (!interface_exists(Wrapper_Interface::class)) {
            throw new RuntimeException('Please install "spomky-labs/aes-key-wrap" to use AES-KW algorithms');
        }
    }
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
    public function wrap_key(JWK $key, string $cek, array $complete_header, array &$additional_header): string
    {
        $kek = $this->get_key($key);
        $iv = random_bytes(96 / 8);
        $additional_header['iv'] = Base64url_Safe::encode_unpadded($iv);
        $mode = sprintf('aes-%d-gcm', $this->get_key_size());
        $tag = '';
        $encrypted_cek = openssl_encrypt($cek, $mode, $kek, OPENSSL_RAW_DATA, $iv, $tag, '');
        if ($encrypted_cek === false) {
            throw new RuntimeException('Unable to encrypt the CEK');
        }
        $additional_header['tag'] = Base64url_Safe::encode_unpadded($tag);
        return $encrypted_cek;
    }
    /**
     * @param array<string, mixed> $completeHeader
     */
    #[Override]
    public function unwrap_key(JWK $key, string $encrypted_cek, array $complete_header): string
    {
        $kek = $this->get_key($key);
        isset($complete_header['iv']) && is_string($complete_header['iv']) || throw new InvalidArgumentException('Parameter "iv" is missing.');
        isset($complete_header['tag']) && is_string($complete_header['tag']) || throw new InvalidArgumentException('Parameter "tag" is missing.');
        $tag = Base64url_Safe::decode_no_padding($complete_header['tag']);
        $iv = Base64url_Safe::decode_no_padding($complete_header['iv']);
        $mode = sprintf('aes-%d-gcm', $this->get_key_size());
        $cek = openssl_decrypt($encrypted_cek, $mode, $kek, OPENSSL_RAW_DATA, $iv, $tag, '');
        if ($cek === false) {
            throw new RuntimeException('Unable to decrypt the CEK');
        }
        return $cek;
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_WRAP;
    }
    protected function get_key(JWK $key): string
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
    abstract protected function get_key_size(): int;
}