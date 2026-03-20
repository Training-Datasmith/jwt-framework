<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Key_Converter;

use function array_key_exists;
use function assert;
use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Big_Integer;
use RuntimeException;
/**
 * @internal
 */
final class Rsa_Key
{
    /**
     * @var array<array-key, string>
     */
    private array $values = [];
    /**
     * @param array<array-key, string> $data
     */
    private function __construct(array $data)
    {
        $this->load_jwk($data);
    }
    /**
     * @param array<array-key, mixed> $details
     */
    public static function create_from_key_details(array $details): self
    {
        $values = ['kty' => 'RSA'];
        $keys = ['n' => 'n', 'e' => 'e', 'd' => 'd', 'p' => 'p', 'q' => 'q', 'dp' => 'dmp1', 'dq' => 'dmq1', 'qi' => 'iqmp'];
        foreach ($details as $key => $value) {
            if (in_array($key, $keys, true)) {
                assert(is_string($value), 'Invalid key.');
                $value = Base64url_Safe::encode_unpadded($value);
                $values[array_search($key, $keys, true)] = $value;
            }
        }
        return new self($values);
    }
    public static function create_from_pem(string $pem): self
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        $res = openssl_pkey_get_private($pem);
        if ($res === false) {
            $res = openssl_pkey_get_public($pem);
        }
        if ($res === false) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        $details = openssl_pkey_get_details($res);
        if (!is_array($details) || !isset($details['rsa'])) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        $data = $details['rsa'];
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        return self::create_from_key_details($data);
    }
    public static function create_from_jwk(JWK $jwk): self
    {
        return new self($jwk->all());
    }
    public function is_public(): bool
    {
        return !array_key_exists('d', $this->values);
    }
    public static function to_public(self $private): self
    {
        $data = $private->to_array();
        $keys = ['p', 'd', 'q', 'dp', 'dq', 'qi'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }
        return new self($data);
    }
    /**
     * @return array<array-key, string>
     */
    public function to_array(): array
    {
        return $this->values;
    }
    public function to_jwk(): JWK
    {
        return new JWK($this->values);
    }
    /**
     * This method will try to add Chinese Remainder Theorem (CRT) parameters. With those primes, the decryption process
     * is really fast.
     */
    public function optimize(): void
    {
        if (array_key_exists('d', $this->values)) {
            $this->populate_crt();
        }
    }
    /**
     * @param array<array-key, string> $jwk
     */
    private function load_jwk(array $jwk): void
    {
        if (!array_key_exists('kty', $jwk)) {
            throw new InvalidArgumentException('The key parameter "kty" is missing.');
        }
        if ($jwk['kty'] !== 'RSA') {
            throw new InvalidArgumentException('The JWK is not a RSA key.');
        }
        $this->values = $jwk;
    }
    /**
     * This method adds Chinese Remainder Theorem (CRT) parameters if primes 'p' and 'q' are available. If 'p' and 'q'
     * are missing, they are computed and added to the key data.
     */
    private function populate_crt(): void
    {
        if (!array_key_exists('p', $this->values) && !array_key_exists('q', $this->values)) {
            $d = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['d']));
            $e = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['e']));
            $n = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['n']));
            [$p, $q] = $this->find_prime_factors($d, $e, $n);
            $this->values['p'] = Base64url_Safe::encode_unpadded($p->to_bytes());
            $this->values['q'] = Base64url_Safe::encode_unpadded($q->to_bytes());
        }
        if (array_key_exists('dp', $this->values) && array_key_exists('dq', $this->values) && array_key_exists('qi', $this->values)) {
            return;
        }
        $one = Big_Integer::create_from_decimal(1);
        $d = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['d']));
        $p = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['p']));
        $q = Big_Integer::create_from_binary_string(Base64url_Safe::decode_no_padding($this->values['q']));
        $this->values['dp'] = Base64url_Safe::encode_unpadded($d->mod($p->subtract($one))->to_bytes());
        $this->values['dq'] = Base64url_Safe::encode_unpadded($d->mod($q->subtract($one))->to_bytes());
        $this->values['qi'] = Base64url_Safe::encode_unpadded($q->mod_inverse($p)->to_bytes());
    }
    /**
     * @return BigInteger[]
     */
    private function find_prime_factors(Big_Integer $d, Big_Integer $e, Big_Integer $n): array
    {
        $zero = Big_Integer::create_from_decimal(0);
        $one = Big_Integer::create_from_decimal(1);
        $two = Big_Integer::create_from_decimal(2);
        $k = $d->multiply($e)->subtract($one);
        if ($k->is_even()) {
            $r = $k;
            $t = $zero;
            do {
                $r = $r->divide($two);
                $t = $t->add($one);
            } while ($r->is_even());
            $found = false;
            $y = null;
            for ($i = 1; $i <= 100; ++$i) {
                $g = Big_Integer::random($n->subtract($one));
                $y = $g->mod_pow($r, $n);
                if ($y->equals($one)) {
                    continue;
                }
                if ($y->equals($n->subtract($one))) {
                    continue;
                }
                for ($j = $one; $j->lower_than($t->subtract($one)); $j = $j->add($one)) {
                    $x = $y->mod_pow($two, $n);
                    if ($x->equals($one)) {
                        $found = true;
                        break;
                    }
                    if ($x->equals($n->subtract($one))) {
                        continue;
                    }
                    $y = $x;
                }
                $x = $y->mod_pow($two, $n);
                if ($x->equals($one)) {
                    $found = true;
                    break;
                }
            }
            if ($y === null) {
                throw new InvalidArgumentException('Unable to find prime factors.');
            }
            if ($found === true) {
                $p = $y->subtract($one)->gcd($n);
                $q = $n->divide($p);
                return [$p, $q];
            }
        }
        throw new InvalidArgumentException('Unable to find prime factors.');
    }
}