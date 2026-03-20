<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use function array_key_exists;
use Brick\Math\Big_Integer;
use function extension_loaded;
use function function_exists;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Ecc\Curve;
use Jose\Component\Core\Util\Ecc\Ec_Dh;
use Jose\Component\Core\Util\Ecc\Nist_Curve;
use Jose\Component\Core\Util\Ecc\Private_Key;
use Jose\Component\Core\Util\Ec_Key;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Concat_Kdf;
use Override;
use RuntimeException;
use function sprintf;
use function strlen;
use Throwable;
abstract readonly class Abstract_Ecdh implements Key_Agreement
{
    #[Override]
    public function allowed_key_types(): array
    {
        return ['EC', 'OKP'];
    }
    /**
     * @param array<string, mixed> $complete_header
     * @param array<string, mixed> $additional_header_values
     */
    #[Override]
    public function get_agreement_key(int $encryption_key_length, string $algorithm, JWK $recipient_key, ?JWK $sender_key, array $complete_header = [], array &$additional_header_values = []): string
    {
        if ($recipient_key->has('d')) {
            [$public_key, $private_key] = $this->get_keys_from_private_key_and_header($recipient_key, $complete_header);
        } else {
            [$public_key, $private_key] = $this->get_keys_from_public_key($recipient_key, $sender_key, $additional_header_values);
        }
        $agreed_key = $this->calculate_agreement_key($private_key, $public_key);
        $apu = array_key_exists('apu', $complete_header) ? $complete_header['apu'] : '';
        is_string($apu) || throw new InvalidArgumentException('Invalid APU.');
        $apv = array_key_exists('apv', $complete_header) ? $complete_header['apv'] : '';
        is_string($apv) || throw new InvalidArgumentException('Invalid APU.');
        return Concat_Kdf::generate($agreed_key, $algorithm, $encryption_key_length, $apu, $apv);
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_AGREEMENT;
    }
    protected function calculate_agreement_key(JWK $private_key, JWK $public_key): string
    {
        $crv = $public_key->get('crv');
        if (!is_string($crv)) {
            throw new InvalidArgumentException('Invalid key parameter "crv"');
        }
        switch ($crv) {
            case 'P-256':
            case 'P-384':
            case 'P-521':
                $curve = $this->get_curve($crv);
                if (function_exists('openssl_pkey_derive')) {
                    try {
                        $public_pem = Ec_Key::convert_public_key_to_pem($public_key);
                        $private_pem = Ec_Key::convert_private_key_to_pem($private_key);
                        $res = openssl_pkey_derive($public_pem, $private_pem);
                        if ($res === false) {
                            throw new RuntimeException('Unable to derive the key');
                        }
                        return $res;
                    } catch (Throwable) {
                        //Does nothing. Will fallback to the pure PHP function
                    }
                }
                $x = $public_key->get('x');
                if (!is_string($x)) {
                    throw new InvalidArgumentException('Invalid key parameter "x"');
                }
                $y = $public_key->get('y');
                if (!is_string($y)) {
                    throw new InvalidArgumentException('Invalid key parameter "y"');
                }
                $d = $private_key->get('d');
                if (!is_string($d)) {
                    throw new InvalidArgumentException('Invalid key parameter "d"');
                }
                $rec_x = $this->convert_base64to_big_integer($x);
                $rec_y = $this->convert_base64to_big_integer($y);
                $sen_d = $this->convert_base64to_big_integer($d);
                $priv_key = Private_Key::create($sen_d);
                $pub_key = $curve->get_public_key_from($rec_x, $rec_y);
                return $this->convert_dec_to_bin(Ec_Dh::compute_shared_key($curve, $pub_key, $priv_key));
            case 'X25519':
                $this->check_sodium_extension_is_available();
                $x = $public_key->get('x');
                if (!is_string($x)) {
                    throw new InvalidArgumentException('Invalid key parameter "x"');
                }
                $d = $private_key->get('d');
                if (!is_string($d)) {
                    throw new InvalidArgumentException('Invalid key parameter "d"');
                }
                $s_key = Base64url_Safe::decode_no_padding($d);
                $recipient_publickey = Base64url_Safe::decode_no_padding($x);
                return sodium_crypto_scalarmult($s_key, $recipient_publickey);
            default:
                throw new InvalidArgumentException(sprintf('The curve "%s" is not supported', $crv));
        }
    }
    /**
     * @param array<string, mixed> $additional_header_values
     * @return JWK[]
     */
    protected function get_keys_from_public_key(JWK $recipient_key, ?JWK $sender_key, array &$additional_header_values): array
    {
        $this->check_key($recipient_key, false);
        $public_key = $recipient_key;
        $crv = $public_key->get('crv');
        if (!is_string($crv)) {
            throw new InvalidArgumentException('Invalid key parameter "crv"');
        }
        $private_key = match ($crv) {
            'P-256', 'P-384', 'P-521' => $sender_key ?? Ec_Key::create_ec_key($crv),
            'X25519' => $sender_key ?? $this->create_okp_key('X25519'),
            default => throw new InvalidArgumentException(sprintf('The curve "%s" is not supported', $crv)),
        };
        $epk = $private_key->to_public()->all();
        $additional_header_values['epk'] = $epk;
        return [$public_key, $private_key];
    }
    /**
     * @param array<string, mixed> $complete_header
     * @return JWK[]
     */
    protected function get_keys_from_private_key_and_header(JWK $recipient_key, array $complete_header): array
    {
        $this->check_key($recipient_key, true);
        $private_key = $recipient_key;
        $public_key = $this->get_public_key($complete_header);
        if ($private_key->get('crv') !== $public_key->get('crv')) {
            throw new InvalidArgumentException('Curves are different');
        }
        return [$public_key, $private_key];
    }
    /**
     * @param array<string, mixed> $complete_header
     */
    private function get_public_key(array $complete_header): JWK
    {
        if (!isset($complete_header['epk'])) {
            throw new InvalidArgumentException('The header parameter "epk" is missing.');
        }
        if (!is_array($complete_header['epk'])) {
            throw new InvalidArgumentException('The header parameter "epk" is not an array of parameters');
        }
        $public_key = new JWK($complete_header['epk']);
        $this->check_key($public_key, false);
        return $public_key;
    }
    private function check_key(JWK $key, bool $is_private): void
    {
        if (!in_array($key->get('kty'), $this->allowed_key_types(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
        foreach (['x', 'crv'] as $k) {
            if (!$key->has($k)) {
                throw new InvalidArgumentException(sprintf('The key parameter "%s" is missing.', $k));
            }
        }
        $crv = $key->get('crv');
        if (!is_string($crv)) {
            throw new InvalidArgumentException('Invalid key parameter "crv"');
        }
        switch ($crv) {
            case 'P-256':
            case 'P-384':
            case 'P-521':
                if (!$key->has('y')) {
                    throw new InvalidArgumentException('The key parameter "y" is missing.');
                }
                break;
            case 'X25519':
                break;
            default:
                throw new InvalidArgumentException(sprintf('The curve "%s" is not supported', $crv));
        }
        if ($is_private === true && !$key->has('d')) {
            throw new InvalidArgumentException('The key parameter "d" is missing.');
        }
    }
    private function get_curve(string $crv): Curve
    {
        return match ($crv) {
            'P-256' => Nist_Curve::curve256(),
            'P-384' => Nist_Curve::curve384(),
            'P-521' => Nist_Curve::curve521(),
            default => throw new InvalidArgumentException(sprintf('The curve "%s" is not supported', $crv)),
        };
    }
    private function convert_base64to_big_integer(string $value): Big_Integer
    {
        $data = unpack('H*', Base64url_Safe::decode_no_padding($value));
        if (!is_array($data) || !isset($data[1]) || !is_string($data[1])) {
            throw new InvalidArgumentException('Unable to convert base64 to integer');
        }
        return Big_Integer::from_base($data[1], 16);
    }
    private function convert_dec_to_bin(Big_Integer $dec): string
    {
        if ($dec->compare_to(Big_Integer::zero()) < 0) {
            throw new InvalidArgumentException('Unable to convert negative integer to string');
        }
        $hex = $dec->to_base(16);
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        $bin = hex2bin($hex);
        if ($bin === false) {
            throw new InvalidArgumentException('Unable to convert integer to string');
        }
        return $bin;
    }
    /**
     * @param string $curve The curve
     */
    private function create_okp_key(string $curve): JWK
    {
        $this->check_sodium_extension_is_available();
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
        return new JWK(['kty' => 'OKP', 'crv' => $curve, 'x' => Base64url_Safe::encode_unpadded($x), 'd' => Base64url_Safe::encode_unpadded($d)]);
    }
    private function check_sodium_extension_is_available(): void
    {
        if (!extension_loaded('sodium')) {
            throw new RuntimeException('The extension "sodium" is not available. Please install it to use this method');
        }
    }
}