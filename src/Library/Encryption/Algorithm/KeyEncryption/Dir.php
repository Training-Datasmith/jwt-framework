<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use function in_array;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
final readonly class Dir implements Direct_Encryption
{
    #[Override]
    public function get_cek(JWK $key): string
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
    #[Override]
    public function name(): string
    {
        return 'dir';
    }
    #[Override]
    public function allowed_key_types(): array
    {
        return ['oct'];
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_DIRECT;
    }
}