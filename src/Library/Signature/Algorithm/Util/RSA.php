<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Algorithm\Util;

use function chr;
use function extension_loaded;
use InvalidArgumentException;
use Jose\Component\Core\Util\Big_Integer;
use Jose\Component\Core\Util\Hash;
use Jose\Component\Core\Util\Rsa_Key;
use function ord;
use RuntimeException;
use const STR_PAD_LEFT;
use function strlen;
/**
 * @internal
 */
final readonly class RSA
{
    /**
     * Probabilistic Signature Scheme.
     */
    public const SIGNATURE_PSS = 1;
    /**
     * Use the PKCS#1.
     */
    public const SIGNATURE_PKCS1 = 2;
    /**
     * @return non-empty-string
     */
    public static function sign(Rsa_Key $key, string $message, string $hash, int $mode): string
    {
        switch ($mode) {
            case self::SIGNATURE_PSS:
                return self::sign_with_pss($key, $message, $hash);
            case self::SIGNATURE_PKCS1:
                if (!extension_loaded('openssl')) {
                    throw new RuntimeException('Please install the OpenSSL extension');
                }
                $result = openssl_sign($message, $signature, $key->to_pem(), $hash);
                if ($result !== true) {
                    throw new RuntimeException('Unable to sign the data');
                }
                return $signature;
            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }
    /**
     * Create a signature.
     *
     * @return non-empty-string
     */
    public static function sign_with_pss(Rsa_Key $key, string $message, string $hash): string
    {
        $em = self::encode_emsapss($message, 8 * $key->get_modulus_length() - 1, Hash::get($hash));
        $message = Big_Integer::create_from_binary_string($em);
        $signature = Rsa_Key::exponentiate($key, $message);
        $result = self::convert_integer_to_octet_string($signature, $key->get_modulus_length());
        if ($result === '') {
            throw new InvalidArgumentException('Invalid signature.');
        }
        return $result;
    }
    public static function verify(Rsa_Key $key, string $message, string $signature, string $hash, int $mode): bool
    {
        switch ($mode) {
            case self::SIGNATURE_PSS:
                return self::verify_with_pss($key, $message, $signature, $hash);
            case self::SIGNATURE_PKCS1:
                if (!extension_loaded('openssl')) {
                    throw new RuntimeException('Please install the OpenSSL extension');
                }
                return openssl_verify($message, $signature, $key->to_pem(), $hash) === 1;
            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }
    /**
     * Verifies a signature.
     */
    public static function verify_with_pss(Rsa_Key $key, string $message, string $signature, string $hash): bool
    {
        if (strlen($signature) !== $key->get_modulus_length()) {
            throw new RuntimeException();
        }
        $s2 = Big_Integer::create_from_binary_string($signature);
        $m2 = Rsa_Key::exponentiate($key, $s2);
        $em = self::convert_integer_to_octet_string($m2, $key->get_modulus_length());
        $mod_bits = 8 * $key->get_modulus_length();
        return self::verify_emsapss($message, $em, $mod_bits - 1, Hash::get($hash));
    }
    private static function convert_integer_to_octet_string(Big_Integer $x, int $x_len): string
    {
        $x = $x->to_bytes();
        if (strlen($x) > $x_len) {
            throw new RuntimeException();
        }
        return str_pad($x, $x_len, chr(0), STR_PAD_LEFT);
    }
    /**
     * MGF1.
     */
    private static function get_mgf1(string $mgf_seed, int $mask_len, Hash $mgf_hash): string
    {
        $t = '';
        $count = ceil($mask_len / $mgf_hash->get_length());
        for ($i = 0; $i < $count; ++$i) {
            $c = pack('N', $i);
            $t .= $mgf_hash->hash($mgf_seed . $c);
        }
        return substr($t, 0, $mask_len);
    }
    /**
     * EMSA-PSS-ENCODE.
     */
    private static function encode_emsapss(string $message, int $modulus_length, Hash $hash): string
    {
        $em_len = $modulus_length + 1 >> 3;
        $s_len = $hash->get_length();
        $m_hash = $hash->hash($message);
        if ($em_len <= $hash->get_length() + $s_len + 2) {
            throw new RuntimeException();
        }
        $salt = random_bytes($s_len);
        $m2 = "\x00\x00\x00\x00\x00\x00\x00\x00" . $m_hash . $salt;
        $h = $hash->hash($m2);
        $ps = str_repeat(chr(0), $em_len - $s_len - $hash->get_length() - 2);
        $db = $ps . chr(1) . $salt;
        $db_mask = self::get_mgf1($h, $em_len - $hash->get_length() - 1, $hash);
        $masked_db = $db ^ $db_mask;
        $masked_db[0] = ~chr(0xff << ($modulus_length & 7)) & $masked_db[0];
        return $masked_db . $h . chr(0xbc);
    }
    /**
     * EMSA-PSS-VERIFY.
     */
    private static function verify_emsapss(string $m, string $em, int $em_bits, Hash $hash): bool
    {
        $em_len = $em_bits + 1 >> 3;
        $s_len = $hash->get_length();
        $m_hash = $hash->hash($m);
        if ($em_len < $hash->get_length() + $s_len + 2) {
            throw new InvalidArgumentException();
        }
        if ($em[strlen($em) - 1] !== chr(0xbc)) {
            throw new InvalidArgumentException();
        }
        $masked_db = substr($em, 0, -$hash->get_length() - 1);
        $h = substr($em, -$hash->get_length() - 1, $hash->get_length());
        $temp = chr(0xff << ($em_bits & 7));
        if ((~$masked_db[0] & $temp) !== $temp) {
            throw new InvalidArgumentException();
        }
        $db_mask = self::get_mgf1($h, $em_len - $hash->get_length() - 1, $hash);
        $db = $masked_db ^ $db_mask;
        $db[0] = ~chr(0xff << ($em_bits & 7)) & $db[0];
        $temp = $em_len - $hash->get_length() - $s_len - 2;
        if (substr($db, 0, $temp) !== str_repeat(chr(0), $temp)) {
            throw new InvalidArgumentException();
        }
        if (ord($db[$temp]) !== 1) {
            throw new InvalidArgumentException();
        }
        $salt = substr($db, $temp + 1);
        // should be $sLen long
        $m2 = "\x00\x00\x00\x00\x00\x00\x00\x00" . $m_hash . $salt;
        $h2 = $hash->hash($m2);
        return hash_equals($h, $h2);
    }
}