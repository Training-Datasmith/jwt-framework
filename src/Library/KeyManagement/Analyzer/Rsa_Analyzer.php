<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
use const STR_PAD_RIGHT;
use function strlen;
final readonly class Rsa_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if ($jwk->get('kty') !== 'RSA') {
            return;
        }
        $this->check_exponent($jwk, $bag);
        $this->check_modulus($jwk, $bag);
    }
    private function check_exponent(JWK $jwk, Message_Bag $bag): void
    {
        $e = $jwk->get('e');
        if (!is_string($e)) {
            $bag->add(Message::high('The exponent is not valid.'));
            return;
        }
        $exponent = unpack('l', str_pad(Base64url_Safe::decode_no_padding($e), 4, "\x00", STR_PAD_RIGHT));
        if (!is_array($exponent) || !isset($exponent[1])) {
            throw new InvalidArgumentException('Unable to get the private key');
        }
        if ($exponent[1] < 65537) {
            $bag->add(Message::high('The exponent is too low. It should be at least 65537.'));
        }
    }
    private function check_modulus(JWK $jwk, Message_Bag $bag): void
    {
        $n = $jwk->get('n');
        if (!is_string($n)) {
            $bag->add(Message::high('The modulus is not valid.'));
            return;
        }
        $n = 8 * strlen(Base64url_Safe::decode_no_padding($n));
        if ($n < 2048) {
            $bag->add(Message::high('The key length is less than 2048 bits.'));
        }
        if ($jwk->has('d') && (!$jwk->has('p') || !$jwk->has('q') || !$jwk->has('dp') || !$jwk->has('dq') || !$jwk->has('qi'))) {
            $bag->add(Message::medium('The key is a private RSA key, but Chinese Remainder Theorem primes are missing. These primes are not mandatory, but signatures and decryption processes are faster when available.'));
        }
    }
}