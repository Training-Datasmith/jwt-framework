<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm;

use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Rsa_Key;
use Override;
use RuntimeException;
use function sprintf;
abstract readonly class RSAPKCS1 implements Signature_Algorithm
{
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
    }
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
        return openssl_verify($input, $signature, $pub->to_pem(), $this->get_algorithm()) === 1;
    }
    #[Override]
    public function sign(JWK $key, string $input): string
    {
        $this->check_key($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The key is not a private key.');
        }
        $priv = Rsa_Key::create_from_jwk($key);
        $result = openssl_sign($input, $signature, $priv->to_pem(), $this->get_algorithm());
        if ($result !== true) {
            throw new RuntimeException('Unable to sign');
        }
        return $signature;
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