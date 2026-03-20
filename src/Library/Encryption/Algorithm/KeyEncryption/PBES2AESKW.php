<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A128KW;
use AESKW\A192KW;
use AESKW\A256KW;
use AESKW\Wrapper as WrapperInterface;
use function in_array;
use InvalidArgumentException;
use function is_int;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
use RuntimeException;
abstract readonly class PBES2AESKW implements Key_Wrapping
{
    public function __construct(private readonly int $salt_size = 64, private readonly int $nb_count = 4096)
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
        $password = $this->get_key($key);
        $this->check_header_algorithm($complete_header);
        $wrapper = $this->get_wrapper();
        $hash_algorithm = $this->get_hash_algorithm();
        $key_size = $this->get_key_size();
        $salt = random_bytes($this->salt_size);
        // We set header parameters
        $additional_header['p2s'] = Base64url_Safe::encode_unpadded($salt);
        $additional_header['p2c'] = $this->nb_count;
        $derived_key = hash_pbkdf2($hash_algorithm, $password, $complete_header['alg'] . "\x00" . $salt, $this->nb_count, $key_size, true);
        return $wrapper::wrap($derived_key, $cek);
    }
    /**
     * @param array<string, mixed> $completeHeader
     */
    #[Override]
    public function unwrap_key(JWK $key, string $encrypted_cek, array $complete_header): string
    {
        $password = $this->get_key($key);
        $this->check_header_algorithm($complete_header);
        $this->check_header_additional_parameters($complete_header);
        $wrapper = $this->get_wrapper();
        $hash_algorithm = $this->get_hash_algorithm();
        $key_size = $this->get_key_size();
        $p2s = $complete_header['p2s'];
        is_string($p2s) || throw new InvalidArgumentException('Invalid salt.');
        $salt = $complete_header['alg'] . "\x00" . Base64url_Safe::decode_no_padding($p2s);
        $count = $complete_header['p2c'];
        is_int($count) || throw new InvalidArgumentException('Invalid counter.');
        $derived_key = hash_pbkdf2($hash_algorithm, $password, $salt, $count, $key_size, true);
        return $wrapper::unwrap($derived_key, $encrypted_cek);
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
    /**
     * @param array<string, mixed> $header
     */
    protected function check_header_algorithm(array $header): void
    {
        if (!isset($header['alg'])) {
            throw new InvalidArgumentException('The header parameter "alg" is missing.');
        }
        if (!is_string($header['alg'])) {
            throw new InvalidArgumentException('The header parameter "alg" is not valid.');
        }
    }
    /**
     * @param array<string, mixed> $header
     */
    protected function check_header_additional_parameters(array $header): void
    {
        if (!isset($header['p2s'])) {
            throw new InvalidArgumentException('The header parameter "p2s" is missing.');
        }
        if (!is_string($header['p2s'])) {
            throw new InvalidArgumentException('The header parameter "p2s" is not valid.');
        }
        if (!isset($header['p2c'])) {
            throw new InvalidArgumentException('The header parameter "p2c" is missing.');
        }
        if (!is_int($header['p2c']) || $header['p2c'] <= 0) {
            throw new InvalidArgumentException('The header parameter "p2c" is not valid.');
        }
        if ($header['p2c'] > 310000) {
            throw new InvalidArgumentException('The header parameter "p2c" exceeds the maximum allowed iteration count.');
        }
    }
    abstract protected function get_wrapper(): A256KW|A128KW|A192KW;
    abstract protected function get_hash_algorithm(): string;
    abstract protected function get_key_size(): int;
}