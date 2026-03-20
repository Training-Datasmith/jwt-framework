<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function is_bool;
use Override;
/**
 * This class is a header parameter checker. When the "b64" is present, it will check if the value is a boolean or not.
 *
 * The use of this checker will allow the use of token with unencoded payload.
 */
final class Unencoded_Payload_Checker implements Header_Checker
{
    private const HEADER_NAME = 'b64';
    #[Override]
    public function check_header(mixed $value): void
    {
        if (!is_bool($value)) {
            throw new Invalid_Header_Exception('"b64" must be a boolean.', self::HEADER_NAME, $value);
        }
    }
    #[Override]
    public function supported_header(): string
    {
        return self::HEADER_NAME;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return true;
    }
}