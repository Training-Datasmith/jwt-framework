<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm;

use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
abstract readonly class HMAC implements Mac_Algorithm
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['oct'];
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
        return hash_hmac($this->get_hash_algorithm(), $input, $k, true);
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
    abstract protected function get_hash_algorithm(): string;
}