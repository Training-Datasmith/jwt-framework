<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption\Util;

use function chr;
use function count;
use InvalidArgumentException;
use Jose\Component\Core\Util\Big_Integer;
use Jose\Component\Core\Util\Hash;
use Jose\Component\Core\Util\Rsa_Key;
use LogicException;
use function ord;
use RuntimeException;
use const STR_PAD_LEFT;
use function strlen;
/**
 * @internal
 */
final readonly class Rsa_Crypt
{
    /**
     * Optimal Asymmetric Encryption Padding (OAEP).
     */
    public const ENCRYPTION_OAEP = 1;
    /**
     * Use PKCS#1 padding.
     */
    public const ENCRYPTION_PKCS1 = 2;
    public static function encrypt(Rsa_Key $key, string $data, int $mode, ?string $hash = null): string
    {
        switch ($mode) {
            case self::ENCRYPTION_OAEP:
                if ($hash === null) {
                    throw new LogicException('Hash shall be defined for RSA OAEP cyphering');
                }
                return self::encrypt_with_rsaoaep($key, $data, $hash);
            case self::ENCRYPTION_PKCS1:
                return self::encrypt_with_rsa15($key, $data);
            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }
    public static function decrypt(Rsa_Key $key, string $plaintext, int $mode, ?string $hash = null): string
    {
        switch ($mode) {
            case self::ENCRYPTION_OAEP:
                if ($hash === null) {
                    throw new LogicException('Hash shall be defined for RSA OAEP cyphering');
                }
                return self::decrypt_with_rsaoaep($key, $plaintext, $hash);
            case self::ENCRYPTION_PKCS1:
                return self::decrypt_with_rsa15($key, $plaintext);
            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }
    public static function encrypt_with_rsa15(Rsa_Key $key, string $data): string
    {
        $m_len = strlen($data);
        if ($m_len > $key->get_modulus_length() - 11) {
            throw new InvalidArgumentException('Message too long');
        }
        $ps_len = $key->get_modulus_length() - $m_len - 3;
        $ps = '';
        while (strlen($ps) !== $ps_len) {
            $temp = random_bytes($ps_len - strlen($ps));
            $temp = str_replace("\x00", '', $temp);
            $ps .= $temp;
        }
        $type = 2;
        $data = chr(0) . chr($type) . $ps . chr(0) . $data;
        $binary_data = Big_Integer::create_from_binary_string($data);
        $c = self::get_rsaep($key, $binary_data);
        return self::convert_integer_to_octet_string($c, $key->get_modulus_length());
    }
    public static function decrypt_with_rsa15(Rsa_Key $key, string $c): string
    {
        if (strlen($c) !== $key->get_modulus_length()) {
            throw new InvalidArgumentException('Unable to decrypt');
        }
        $c = Big_Integer::create_from_binary_string($c);
        $m = self::get_rsadp($key, $c);
        $em = self::convert_integer_to_octet_string($m, $key->get_modulus_length());
        if (ord($em[0]) !== 0 || ord($em[1]) > 2) {
            throw new InvalidArgumentException('Unable to decrypt');
        }
        $ps = substr($em, 2, (int) strpos($em, chr(0), 2) - 2);
        $m = substr($em, strlen($ps) + 3);
        if (strlen($ps) < 8) {
            throw new InvalidArgumentException('Unable to decrypt');
        }
        return $m;
    }
    /**
     * Encryption.
     */
    public static function encrypt_with_rsaoaep(Rsa_Key $key, string $plaintext, string $hash_algorithm): string
    {
        /** @var Hash $hash */
        $hash = Hash::$hash_algorithm();
        $length = $key->get_modulus_length() - 2 * $hash->get_length() - 2;
        if ($length <= 0) {
            throw new RuntimeException();
        }
        $split_plaintext = str_split($plaintext, $length);
        $ciphertext = '';
        foreach ($split_plaintext as $m) {
            $ciphertext .= self::encrypt_rsaesoaep($key, $m, $hash);
        }
        return $ciphertext;
    }
    /**
     * Decryption.
     */
    public static function decrypt_with_rsaoaep(Rsa_Key $key, string $ciphertext, string $hash_algorithm): string
    {
        if ($key->get_modulus_length() <= 0) {
            throw new RuntimeException('Invalid modulus length');
        }
        $hash = Hash::$hash_algorithm();
        $split_ciphertext = str_split($ciphertext, $key->get_modulus_length());
        $split_ciphertext[count($split_ciphertext) - 1] = str_pad($split_ciphertext[count($split_ciphertext) - 1], $key->get_modulus_length(), chr(0), STR_PAD_LEFT);
        $plaintext = '';
        foreach ($split_ciphertext as $c) {
            $temp = self::get_rsaesoaep($key, $c, $hash);
            $plaintext .= $temp;
        }
        return $plaintext;
    }
    private static function convert_integer_to_octet_string(Big_Integer $x, int $x_len): string
    {
        $x = $x->to_bytes();
        if (strlen($x) > $x_len) {
            throw new RuntimeException('Invalid length.');
        }
        return str_pad($x, $x_len, chr(0), STR_PAD_LEFT);
    }
    /**
     * Octet-String-to-Integer primitive.
     */
    private static function convert_octet_string_to_integer(string $x): Big_Integer
    {
        return Big_Integer::create_from_binary_string($x);
    }
    /**
     * RSA EP.
     */
    private static function get_rsaep(Rsa_Key $key, Big_Integer $m): Big_Integer
    {
        if ($m->compare(Big_Integer::create_from_decimal(0)) < 0 || $m->compare($key->get_modulus()) > 0) {
            throw new RuntimeException();
        }
        return Rsa_Key::exponentiate($key, $m);
    }
    /**
     * RSA DP.
     */
    private static function get_rsadp(Rsa_Key $key, Big_Integer $c): Big_Integer
    {
        if ($c->compare(Big_Integer::create_from_decimal(0)) < 0 || $c->compare($key->get_modulus()) > 0) {
            throw new RuntimeException();
        }
        return Rsa_Key::exponentiate($key, $c);
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
     * RSAES-OAEP-ENCRYPT.
     */
    private static function encrypt_rsaesoaep(Rsa_Key $key, string $m, Hash $hash): string
    {
        $m_len = strlen($m);
        $l_hash = $hash->hash('');
        $ps = str_repeat(chr(0), $key->get_modulus_length() - $m_len - 2 * $hash->get_length() - 2);
        $db = $l_hash . $ps . chr(1) . $m;
        $seed = random_bytes($hash->get_length());
        $db_mask = self::get_mgf1($seed, $key->get_modulus_length() - $hash->get_length() - 1, $hash);
        $masked_db = $db ^ $db_mask;
        $seed_mask = self::get_mgf1($masked_db, $hash->get_length(), $hash);
        $masked_seed = $seed ^ $seed_mask;
        $em = chr(0) . $masked_seed . $masked_db;
        $m = self::convert_octet_string_to_integer($em);
        $c = self::get_rsaep($key, $m);
        return self::convert_integer_to_octet_string($c, $key->get_modulus_length());
    }
    /**
     * RSAES-OAEP-DECRYPT.
     */
    private static function get_rsaesoaep(Rsa_Key $key, string $c, Hash $hash): string
    {
        $c = self::convert_octet_string_to_integer($c);
        $m = self::get_rsadp($key, $c);
        $em = self::convert_integer_to_octet_string($m, $key->get_modulus_length());
        $l_hash = $hash->hash('');
        $masked_seed = substr($em, 1, $hash->get_length());
        $masked_db = substr($em, $hash->get_length() + 1);
        $seed_mask = self::get_mgf1($masked_db, $hash->get_length(), $hash);
        $seed = $masked_seed ^ $seed_mask;
        $db_mask = self::get_mgf1($seed, $key->get_modulus_length() - $hash->get_length() - 1, $hash);
        $db = $masked_db ^ $db_mask;
        $l_hash2 = substr($db, 0, $hash->get_length());
        $m = substr($db, $hash->get_length());
        if (!hash_equals($l_hash, $l_hash2)) {
            throw new RuntimeException();
        }
        $m = ltrim($m, chr(0));
        if (ord($m[0]) !== 1) {
            throw new RuntimeException();
        }
        return substr($m, 1);
    }
}