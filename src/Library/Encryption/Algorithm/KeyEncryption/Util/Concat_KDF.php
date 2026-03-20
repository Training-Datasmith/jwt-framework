<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption\Util;

use InvalidArgumentException;
use Jose\Component\Core\Util\Base64url_Safe;
use const STR_PAD_LEFT;
use function strlen;
/**
 * @internal
 *
 * @see https://tools.ietf.org/html/rfc7518#section-4.6.2
 */
final readonly class Concat_Kdf
{
    /**
     * Key Derivation Function.
     *
     * @param string $Z Shared secret
     * @param string $algorithm Encryption algorithm
     * @param int $encryption_key_size Size of the encryption key
     * @param string $apu Agreement PartyUInfo (information about the producer)
     * @param string $apv Agreement PartyVInfo (information about the recipient)
     */
    public static function generate(string $Z, string $algorithm, int $encryption_key_size, string $apu = '', string $apv = ''): string
    {
        $apu = !self::is_empty($apu) ? Base64url_Safe::decode_no_padding($apu) : '';
        $apv = !self::is_empty($apv) ? Base64url_Safe::decode_no_padding($apv) : '';
        $encryption_segments = [
            self::to_int32bits(1),
            // Round number 1
            $Z,
            // Z (shared secret)
            self::to_int32bits(strlen($algorithm)) . $algorithm,
            // Size of algorithm's name and algorithm
            self::to_int32bits(strlen($apu)) . $apu,
            // PartyUInfo
            self::to_int32bits(strlen($apv)) . $apv,
            // PartyVInfo
            self::to_int32bits($encryption_key_size),
            // SuppPubInfo (the encryption key size)
            '',
        ];
        $input = implode('', $encryption_segments);
        $hash = hash('sha256', $input, true);
        return substr($hash, 0, $encryption_key_size / 8);
    }
    /**
     * Convert an integer into a 32 bits string.
     *
     * @param int $value Integer to convert
     */
    private static function to_int32bits(int $value): string
    {
        $result = hex2bin(str_pad(dechex($value), 8, '0', STR_PAD_LEFT));
        if ($result === false) {
            throw new InvalidArgumentException('Invalid result');
        }
        return $result;
    }
    private static function is_empty(?string $value): bool
    {
        return $value === null || $value === '';
    }
}