<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\JWK;
use Override;
final readonly class Algorithm_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if (!$jwk->has('alg')) {
            $bag->add(Message::medium('The parameter "alg" should be added.'));
        }
    }
}