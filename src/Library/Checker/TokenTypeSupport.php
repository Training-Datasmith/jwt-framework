<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use Jose\Component\Core\JWT;
/**
 * This interface is used to support token types.
 *
 * The token type is a way to define the format of the token.
 * For example, the JWE token type is used to define the format of the token when it is encrypted.
 * The JWS token type is used to define the format of the token when it is signed.
 */
interface Token_Type_Support
{
    /**
     * This method will retrieve the protect and unprotected headers of the token for the given index. The index is
     * useful when the token is serialized using the Json General Serialization mode. For example the JWE Json General
     * Serialization Mode allows several recipients to be set. The unprotected headers correspond to the share
     * unprotected header and the selected recipient header.
     *
     * @param array<string, mixed> $protectedHeader
     * @param array<string, mixed> $unprotectedHeader
     */
    public function retrieve_token_headers(JWT $jwt, int $index, array &$protected_header, array &$unprotected_header): void;
    /**
     * This method returns true if the token in argument is supported, otherwise false.
     */
    public function supports(JWT $jwt): bool;
}