<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Override;
use function strlen;
final readonly class Oct_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if ($jwk->get('kty') !== 'oct') {
            return;
        }
        $k = $jwk->get('k');
        if (!is_string($k)) {
            $bag->add(Message::high('The key is not valid'));
            return;
        }
        $k = Base64url_Safe::decode_no_padding($k);
        $k_length = 8 * strlen($k);
        if ($k_length < 128) {
            $bag->add(Message::high('The key length is less than 128 bits.'));
        }
    }
}