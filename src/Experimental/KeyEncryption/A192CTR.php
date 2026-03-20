<?php

declare (strict_types=1);
namespace Jose\Experimental\Key_Encryption;

use Override;
final readonly class A192CTR extends AESCTR
{
    #[Override]
    public function name(): string
    {
        return 'A192CTR';
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-192-ctr';
    }
}