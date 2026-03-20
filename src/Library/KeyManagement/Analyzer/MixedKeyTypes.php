<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\Jwk_Set;
use Override;
final class Mixed_Key_Types implements Keyset_Analyzer
{
    #[Override]
    public function analyze(Jwk_Set $jwkset, Message_Bag $bag): void
    {
        if ($jwkset->count() === 0) {
            return;
        }
        $has_symmetric_keys = false;
        $has_asymmetric_keys = false;
        foreach ($jwkset as $jwk) {
            switch ($jwk->get('kty')) {
                case 'oct':
                    $has_symmetric_keys = true;
                    break;
                case 'OKP':
                case 'RSA':
                case 'EC':
                    $has_asymmetric_keys = true;
                    break;
            }
        }
        if ($has_asymmetric_keys && $has_symmetric_keys) {
            $bag->add(Message::medium('This key set mixes symmetric and asymmetric keys.'));
        }
    }
}