<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\Jwk_Set;
use Override;
final readonly class Mixed_Public_And_Private_Keys implements Keyset_Analyzer
{
    #[Override]
    public function analyze(Jwk_Set $jwkset, Message_Bag $bag): void
    {
        if ($jwkset->count() === 0) {
            return;
        }
        $has_public_keys = false;
        $has_private_keys = false;
        foreach ($jwkset as $jwk) {
            switch ($jwk->get('kty')) {
                case 'OKP':
                case 'RSA':
                case 'EC':
                    if ($jwk->has('d')) {
                        $has_private_keys = true;
                    } else {
                        $has_public_keys = true;
                    }
                    break;
            }
        }
        if ($has_private_keys && $has_public_keys) {
            $bag->add(Message::high('This key set mixes public and private keys.'));
        }
    }
}