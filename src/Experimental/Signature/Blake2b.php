<?php

declare (strict_types=1);
namespace Jose\Experimental\Signature;

use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Signature\Algorithm\Mac_Algorithm;
use Override;
use RuntimeException;
use function strlen;
/**
 * @see \Jose\Tests\Component\Signature\Algorithm\Blake2bTest
 */
final readonly class Blake2b implements Mac_Algorithm
{
    private const MINIMUM_KEY_LENGTH = 32;
    public function __construct()
    {
        if (!extension_loaded('sodium')) {
            throw new RuntimeException('Please install the Sodium extension');
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
        return 'BLAKE2B';
    }
    #[Override]
    public function verify(JWK $key, string $input, string $signature): bool
    {
        return hash_equals($this->hash($key, $input), $signature);
    }
    #[Override]
    public function hash(JWK $key, string $input): string
    {
        $k = $this->get_key($key);
        return sodium_crypto_generichash($input, $k);
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
        $key = Base64url_Safe::decode_no_padding($k);
        if (strlen($key) < self::MINIMUM_KEY_LENGTH) {
            throw new InvalidArgumentException('Key provided is shorter than 256 bits.');
        }
        return $key;
    }
}