<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm;

use function defined;
use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Ec_Key;
use Jose\Component\Core\Util\Ec_Signature;
use LogicException;
use Override;
use RuntimeException;
use function sprintf;
use Throwable;
abstract readonly class ECDSA implements Signature_Algorithm
{
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        if (!defined('OPENSSL_KEYTYPE_EC')) {
            throw new LogicException('Elliptic Curve key type not supported by your environment.');
        }
    }
    #[Override]
    public function allowed_key_types(): array
    {
        return ['EC'];
    }
    #[Override]
    public function sign(JWK $key, string $input): string
    {
        $this->check_key($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The EC key is not private');
        }
        $pem = Ec_Key::convert_private_key_to_pem($key);
        openssl_sign($input, $signature, $pem, $this->get_hash_algorithm());
        return Ec_Signature::from_asn1($signature, $this->get_signature_part_length());
    }
    #[Override]
    public function verify(JWK $key, string $input, string $signature): bool
    {
        $this->check_key($key);
        try {
            $der = Ec_Signature::to_asn1($signature, $this->get_signature_part_length());
            $pem = Ec_Key::convert_public_key_to_pem($key);
            return openssl_verify($input, $der, $pem, $this->get_hash_algorithm()) === 1;
        } catch (Throwable) {
            return false;
        }
    }
    abstract protected function get_hash_algorithm(): string;
    abstract protected function get_signature_part_length(): int;
    private function check_key(JWK $key): void
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
        foreach (['x', 'y', 'crv'] as $k) {
            if (!$key->has($k)) {
                throw new InvalidArgumentException(sprintf('The key parameter "%s" is missing.', $k));
            }
        }
    }
}