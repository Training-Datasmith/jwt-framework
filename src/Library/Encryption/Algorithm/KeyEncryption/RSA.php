<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Rsa_Key;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Rsa_Crypt;
use Override;
abstract readonly class RSA implements Key_Encryption
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['RSA'];
    }
    /**
     * @param array<string, mixed> $completeHeader
     * @param array<string, mixed> $additionalHeader
     */
    #[Override]
    public function encrypt_key(JWK $key, string $cek, array $complete_header, array &$additional_header): string
    {
        $this->check_key($key);
        $pub = Rsa_Key::to_public(Rsa_Key::create_from_jwk($key));
        return Rsa_Crypt::encrypt($pub, $cek, $this->get_encryption_mode(), $this->get_hash_algorithm());
    }
    /**
     * @param array<string, mixed> $header
     */
    #[Override]
    public function decrypt_key(JWK $key, string $encrypted_cek, array $header): string
    {
        $this->check_key($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The key is not a private key');
        }
        $priv = Rsa_Key::create_from_jwk($key);
        return Rsa_Crypt::decrypt($priv, $encrypted_cek, $this->get_encryption_mode(), $this->get_hash_algorithm());
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_ENCRYPT;
    }
    protected function check_key(JWK $key): void
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
    }
    abstract protected function get_encryption_mode(): int;
    abstract protected function get_hash_algorithm(): ?string;
}