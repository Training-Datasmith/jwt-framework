<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use Jose\Component\Checker\Token_Type_Support;
use Jose\Component\Core\JWT;
use Override;
final class Jwe_Token_Support implements Token_Type_Support
{
    #[Override]
    public function supports(JWT $jwt): bool
    {
        return $jwt instanceof JWE;
    }
    /**
     * @param array<string, mixed> $protectedHeader
     * @param array<string, mixed> $unprotectedHeader
     */
    #[Override]
    public function retrieve_token_headers(JWT $jwt, int $index, array &$protected_header, array &$unprotected_header): void
    {
        if (!$jwt instanceof JWE) {
            return;
        }
        $protected_header = $jwt->get_shared_protected_header();
        $unprotected_header = $jwt->get_shared_header();
        $recipient = $jwt->get_recipient($index)->get_header();
        $unprotected_header = array_merge($unprotected_header, $recipient);
    }
}