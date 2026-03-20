<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\Wrapper as WrapperInterface;
use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
use RuntimeException;
abstract readonly class AESKW implements Key_Wrapping
{
    public function __construct()
    {
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
        $k = $this->get_key($key);
        $wrapper = $this->get_wrapper();
        return $wrapper::wrap($k, $cek);
    }
    /**
     * @param array<string, mixed> $completeHeader
     */
    #[Override]
    public function unwrap_key(JWK $key, string $encrypted_cek, array $complete_header): string
    {
        $k = $this->get_key($key);
        $wrapper = $this->get_wrapper();
        return $wrapper::unwrap($k, $encrypted_cek);
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_WRAP;
    }
    abstract protected function get_wrapper(): Wrapper_Interface;
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