<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\JWK;
use Override;
final readonly class None_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if ($jwk->get('kty') !== 'none') {
            return;
        }
        $bag->add(Message::high('This key is a meant to be used with the algorithm "none". This algorithm is not secured and should be used with care.'));
    }
}