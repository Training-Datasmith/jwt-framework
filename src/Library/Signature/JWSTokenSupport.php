<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use InvalidArgumentException;
use Jose\Component\Checker\Token_Type_Support;
use Jose\Component\Core\JWT;
use Override;
final class Jws_Token_Support implements Token_Type_Support
{
    #[Override]
    public function supports(JWT $jwt): bool
    {
        return $jwt instanceof JWS;
    }
    /**
     * @param array<string, mixed> $protectedHeader
     * @param array<string, mixed> $unprotectedHeader
     */
    #[Override]
    public function retrieve_token_headers(JWT $jwt, int $index, array &$protected_header, array &$unprotected_header): void
    {
        if (!$jwt instanceof JWS) {
            return;
        }
        if ($index > $jwt->count_signatures()) {
            throw new InvalidArgumentException('Unknown signature index.');
        }
        $protected_header = $jwt->get_signature($index)->get_protected_header();
        $unprotected_header = $jwt->get_signature($index)->get_header();
    }
}