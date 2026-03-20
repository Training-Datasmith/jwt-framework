<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\JWK;
use Override;
final readonly class Key_Identifier_Analyzer implements Key_Analyzer
{
    #[Override]
    public function analyze(JWK $jwk, Message_Bag $bag): void
    {
        if (!$jwk->has('kid')) {
            $bag->add(Message::medium('The parameter "kid" should be added.'));
        }
    }
}