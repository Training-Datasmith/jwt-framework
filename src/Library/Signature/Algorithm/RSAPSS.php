<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm;

use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Rsa_Key;
use Jose\Component\Signature\Algorithm\Util\RSA as JoseRSA;
use Override;
use function sprintf;
abstract readonly class RSAPSS implements Signature_Algorithm
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['RSA'];
    }
    #[Override]
    public function verify(JWK $key, string $input, string $signature): bool
    {
        $this->check_key($key);
        $pub = Rsa_Key::create_from_jwk($key->to_public());
        return Jose_Rsa::verify($pub, $input, $signature, $this->get_algorithm(), Jose_Rsa::SIGNATURE_PSS);
    }
    /**
     * @return non-empty-string
     */
    #[Override]
    public function sign(JWK $key, string $input): string
    {
        $this->check_key($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The key is not a private key.');
        }
        $priv = Rsa_Key::create_from_jwk($key);
        return Jose_Rsa::sign($priv, $input, $this->get_algorithm(), Jose_Rsa::SIGNATURE_PSS);
    }
    abstract protected function get_algorithm(): string;
    private function check_key(JWK $key): void
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
        foreach (['n', 'e'] as $k) {
            if (!$key->has($k)) {
                throw new InvalidArgumentException(sprintf('The key parameter "%s" is missing.', $k));
            }
        }
    }
}