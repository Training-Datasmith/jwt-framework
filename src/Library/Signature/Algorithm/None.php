<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm;

use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Override;
final readonly class None implements Signature_Algorithm
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['none'];
    }
    #[Override]
    public function sign(JWK $key, string $input): string
    {
        $this->check_key($key);
        return '';
    }
    #[Override]
    public function verify(JWK $key, string $input, string $signature): bool
    {
        return $signature === '';
    }
    #[Override]
    public function name(): string
    {
        return 'none';
    }
    private function check_key(JWK $key): void
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
    }
}