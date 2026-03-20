<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Serializer;

use function array_key_exists;
abstract readonly class Serializer implements Jws_Serializer
{
    /**
     * @param array<string, mixed> $protectedHeader
     */
    protected function is_payload_encoded(array $protected_header): bool
    {
        return !array_key_exists('b64', $protected_header) || $protected_header['b64'] === true;
    }
}