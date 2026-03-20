<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management;

use function array_key_exists;
use function extension_loaded;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Ec_Key;
use Jose\Component\Key_Management\Key_Converter\Key_Converter;
use Jose\Component\Key_Management\Key_Converter\Rsa_Key;
use const JSON_THROW_ON_ERROR;
use const OPENSSL_KEYTYPE_RSA;
use Open_Ssl_Certificate;
use RuntimeException;
use function sprintf;
use function strlen;
use Throwable;
/**
 * @see \Jose\Tests\Component\KeyManagement\JWKFactoryTest
 */
class Jwk_Factory
{
    /**
     * Creates a RSA key with the given key size and additional values.
     *
     * @param int $size The key size in bits
     * @param array<string, mixed> $values values to configure the key
     */
    public static function create_rsa_key(int $size, array $values = []): JWK
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        if ($size % 8 !== 0) {
            throw new InvalidArgumentException('Invalid key size.');
        }
        if ($size < 512) {
            throw new InvalidArgumentException('Key length is too short. It needs to be at least 512 bits.');
        }
        $key = openssl_pkey_new(['private_key_bits' => $size, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($key === false) {
            throw new InvalidArgumentException('Unable to create the key');
        }
        $details = openssl_pkey_get_details($key);
        if (!is_array($details)) {
            throw new InvalidArgumentException('Unable to create the key');
        }
        $rsa = Rsa_Key::create_from_key_details($details['rsa']);
        $values = array_merge($values, $rsa->to_array());
        return new JWK($values);
    }
    /**
     * Creates a EC key with the given curve and additional values.
     *
     * @param string $curve The curve
     * @param array<string, mixed> $values values to configure the key
     */
    public static function create_ec_key(string $curve, array $values = []): JWK
    {
        return Ec_Key::create_ec_key($curve, $values);
    }
    /**
     * Creates a octet key with the given key size and additional values.
     *
     * @param int $size The key size in bits
     * @param array<string, mixed> $values values to configure the key
     */
    public static function create_oct_key(int $size, array $values = []): JWK
    {
        if ($size % 8 !== 0) {
            throw new InvalidArgumentException('Invalid key size.');
        }
        return self::create_from_secret(random_bytes($size / 8), $values);
    }
    /**
     * Creates a OKP key with the given curve and additional values.
     *
     * @param string $curve The curve
     * @param array<string, mixed> $values values to configure the key
     */
    public static function create_okp_key(string $curve, array $values = []): JWK
    {
        if (!extension_loaded('sodium')) {
            throw new RuntimeException('The extension "sodium" is not available. Please install it to use this method');
        }
        switch ($curve) {
            case 'X25519':
                $key_pair = sodium_crypto_box_keypair();
                $d = sodium_crypto_box_secretkey($key_pair);
                $x = sodium_crypto_box_publickey($key_pair);
                break;
            case 'Ed25519':
                $key_pair = sodium_crypto_sign_keypair();
                $secret = sodium_crypto_sign_secretkey($key_pair);
                $secret_length = strlen($secret);
                $d = substr($secret, 0, -$secret_length / 2);
                $x = sodium_crypto_sign_publickey($key_pair);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported "%s" curve', $curve));
        }
        $values = [...$values, 'kty' => 'OKP', 'crv' => $curve, 'd' => Base64url_Safe::encode_unpadded($d), 'x' => Base64url_Safe::encode_unpadded($x)];
        return new JWK($values);
    }
    /**
     * Creates a none key with the given additional values. Please note that this key type is not part of any
     * specification. It is used to prevent the use of the "none" algorithm with other key types.
     *
     * @param array<string, mixed> $values values to configure the key
     */
    public static function create_none_key(array $values = []): JWK
    {
        $values = [...$values, 'kty' => 'none', 'alg' => 'none', 'use' => 'sig'];
        return new JWK($values);
    }
    /**
     * Creates a key from a Json string.
     */
    public static function create_from_json_object(string $value): JWK|Jwk_Set
    {
        $json = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid key or key set.');
        }
        return self::create_from_values($json);
    }
    /**
     * Creates a key or key set from the given input.
     */
    public static function create_from_values(array $values): JWK|Jwk_Set
    {
        if (array_key_exists('keys', $values) && is_array($values['keys'])) {
            return Jwk_Set::create_from_key_data($values);
        }
        return new JWK($values);
    }
    /**
     * This method create a JWK object using a shared secret.
     */
    public static function create_from_secret(string $secret, array $additional_values = []): JWK
    {
        $values = array_merge($additional_values, ['kty' => 'oct', 'k' => Base64url_Safe::encode_unpadded($secret)]);
        return new JWK($values);
    }
    /**
     * This method will try to load a X.509 certificate and convert it into a public key.
     */
    public static function create_from_certificate_file(string $file, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_key_from_certificate_file($file);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
    /**
     * Extract a keyfrom a key set identified by the given index .
     */
    public static function create_from_key_set(Jwk_Set $jwkset, int|string $index): JWK
    {
        return $jwkset->get($index);
    }
    /**
     * This method will try to load a PKCS#12 file and convert it into a public key.
     */
    public static function create_from_pkcs12certificate_file(string $file, string $secret = '', array $additional_values = []): JWK
    {
        try {
            $content = file_get_contents($file);
            if (!is_string($content)) {
                throw new RuntimeException('Unable to read the file.');
            }
            openssl_pkcs12_read($content, $certs, $secret);
            if (!is_array($certs) || !array_key_exists('pkey', $certs)) {
                throw new RuntimeException('Unable to load the certificates.');
            }
            return self::create_from_key($certs['pkey'], null, $additional_values);
        } catch (Throwable $throwable) {
            throw new RuntimeException('Unable to load the certificates.', $throwable->get_code(), $throwable);
        }
    }
    /**
     * This method will try to convert a X.509 certificate into a public key.
     */
    public static function create_from_certificate(string $certificate, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_key_from_certificate($certificate);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
    /**
     * This method will try to convert a X.509 certificate resource into a public key.
     */
    public static function create_from_x509resource(Open_Ssl_Certificate $res, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_key_from_x509resource($res);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
    /**
     * This method will try to load and convert a key file into a JWK object. If the key is encrypted, the password must
     * be set.
     */
    public static function create_from_key_file(string $file, ?string $password = null, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_from_key_file($file, $password);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
    /**
     * This method will try to load and convert a key into a JWK object. If the key is encrypted, the password must be
     * set.
     */
    public static function create_from_key(string $key, ?string $password = null, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_from_key($key, $password);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
    /**
     * This method will try to load and convert a X.509 certificate chain into a public key.
     *
     * Be careful! The certificate chain is loaded, but it is NOT VERIFIED by any mean! It is mandatory to verify the
     * root CA or intermediate  CA are trusted. If not done, it may lead to potential security issues.
     */
    public static function create_from_x5c(array $x5c, array $additional_values = []): JWK
    {
        $values = Key_Converter::load_from_x5c($x5c);
        $values = array_merge($values, $additional_values);
        return new JWK($values);
    }
}