<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
use function sprintf;
use function strlen;
abstract readonly class Hs_Key_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if ($jwk->get('kty') !== 'oct') {
            return;
        }
        if (!$jwk->has('alg') || $jwk->get('alg') !== $this->get_algorithm_name()) {
            return;
        }
        $k = $jwk->get('k');
        if (!is_string($k)) {
            $bag->add(Message::high('The key is not valid'));
            return;
        }
        $k = Base64url_Safe::decode_no_padding($k);
        $k_length = 8 * strlen($k);
        if ($k_length < $this->get_minimum_key_size()) {
            $bag->add(Message::high(sprintf('HS512 algorithm requires at least %d bits key length.', $this->get_minimum_key_size())));
        }
    }
    abstract protected function get_algorithm_name(): string;
    abstract protected function get_minimum_key_size(): int;
}