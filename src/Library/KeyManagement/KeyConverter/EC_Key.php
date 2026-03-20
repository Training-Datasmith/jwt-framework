<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Key_Converter;

use function array_key_exists;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\Util\Base64url_Safe;
use Spomky_Labs\Pki\Crypto_Encoding\PEM;
use Spomky_Labs\Pki\Crypto_Types\Asymmetric\EC\Ec_Private_Key;
use Spomky_Labs\Pki\Crypto_Types\Asymmetric\EC\Ec_Public_Key;
use Throwable;
/**
 * @internal
 */
final class Ec_Key
{
    private array $values = [];
    private function __construct(array $data)
    {
        $this->load_jwk($data);
    }
    public static function create_from_pem(string $pem): self
    {
        $data = self::load_pem($pem);
        return new self($data);
    }
    public static function to_public(self $private): self
    {
        $data = $private->to_array();
        if (array_key_exists('d', $data)) {
            unset($data['d']);
        }
        return new self($data);
    }
    public function to_array(): array
    {
        return $this->values;
    }
    private static function load_pem(string $data): array
    {
        $pem = PEM::from_string($data);
        try {
            $key = Ec_Private_Key::from_pem($pem);
            return ['kty' => 'EC', 'crv' => self::get_curve($key->named_curve()), 'd' => Base64url_Safe::encode_unpadded($key->private_key_octets()), 'x' => Base64url_Safe::encode_unpadded($key->public_key()->curve_point_octets()[0]), 'y' => Base64url_Safe::encode_unpadded($key->public_key()->curve_point_octets()[1])];
        } catch (Throwable) {
        }
        try {
            $key = Ec_Public_Key::from_pem($pem);
            return ['kty' => 'EC', 'crv' => self::get_curve($key->named_curve()), 'x' => Base64url_Safe::encode_unpadded($key->curve_point_octets()[0]), 'y' => Base64url_Safe::encode_unpadded($key->curve_point_octets()[1])];
        } catch (Throwable) {
        }
        throw new InvalidArgumentException('Unable to load the key.');
    }
    private static function get_curve(string $oid): string
    {
        $curves = self::get_supported_curves();
        $curve = array_search($oid, $curves, true);
        if (!is_string($curve)) {
            throw new InvalidArgumentException('Unsupported OID.');
        }
        return $curve;
    }
    private static function get_supported_curves(): array
    {
        return ['P-256' => '1.2.840.10045.3.1.7', 'P-384' => '1.3.132.0.34', 'P-521' => '1.3.132.0.35'];
    }
    private function load_jwk(array $jwk): void
    {
        $keys = ['kty' => 'The key parameter "kty" is missing.', 'crv' => 'Curve parameter is missing', 'x' => 'Point parameters are missing.', 'y' => 'Point parameters are missing.'];
        foreach ($keys as $k => $v) {
            if (!array_key_exists($k, $jwk)) {
                throw new InvalidArgumentException($v);
            }
        }
        if ($jwk['kty'] !== 'EC') {
            throw new InvalidArgumentException('JWK is not an Elliptic Curve key.');
        }
        $this->values = $jwk;
    }
}