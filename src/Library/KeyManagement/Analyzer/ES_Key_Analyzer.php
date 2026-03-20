<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Brick\Math\Big_Integer;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Ecc\Curve;
use Override;
use function sprintf;
use function strlen;
abstract readonly class Es_Key_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if ($jwk->get('kty') !== 'EC') {
            return;
        }
        if (!$jwk->has('crv')) {
            $bag->add(Message::high('Invalid key. The components "crv" is missing.'));
            return;
        }
        if ($jwk->get('crv') !== $this->get_curve_name()) {
            return;
        }
        $x = $jwk->get('x');
        if (!is_string($x)) {
            $bag->add(Message::high('Invalid key. The components "x" shall be a string.'));
            return;
        }
        $x = Base64url_Safe::decode_no_padding($x);
        $x_length = 8 * strlen($x);
        $y = $jwk->get('y');
        if (!is_string($y)) {
            $bag->add(Message::high('Invalid key. The components "y" shall be a string.'));
            return;
        }
        $y = Base64url_Safe::decode_no_padding($y);
        $y_length = 8 * strlen($y);
        if ($y_length !== $x_length || $y_length !== $this->get_key_size()) {
            $bag->add(Message::high(sprintf('Invalid key. The components "x" and "y" size shall be %d bits.', $this->get_key_size())));
        }
        $x_bi = Big_Integer::from_base(bin2hex($x), 16);
        $y_bi = Big_Integer::from_base(bin2hex($y), 16);
        if (!$this->get_curve()->contains($x_bi, $y_bi)) {
            $bag->add(Message::high('Invalid key. The point is not on the curve.'));
        }
    }
    abstract protected function get_algorithm_name(): string;
    abstract protected function get_curve_name(): string;
    abstract protected function get_curve(): Curve;
    abstract protected function get_key_size(): int;
}