<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function call_user_func;
use InvalidArgumentException;
use function is_callable;
use Override;
use function sprintf;
/**
 * This class is responsible for checking claims and headers using a callable function.
 * @see \Jose\Tests\Component\Checker\CallableCheckerTest
 */
final class Callable_Checker implements Claim_Checker, Header_Checker
{
    /**
     * @param string     $key      The claim or header parameter name to check.
     * @param callable(mixed $value): bool $callable The callable function that will be invoked.
     */
    public function __construct(private readonly string $key, private $callable, private readonly bool $protected_header_only = true)
    {
        if (!is_callable($this->callable)) {
            throw new InvalidArgumentException('The $callable argument must be a callable.');
        }
    }
    #[Override]
    public function check_claim(mixed $value): void
    {
        if (call_user_func($this->callable, $value) !== true) {
            throw new Invalid_Claim_Exception(sprintf('The "%s" claim is invalid.', $this->key), $this->key, $value);
        }
    }
    #[Override]
    public function supported_claim(): string
    {
        return $this->key;
    }
    #[Override]
    public function check_header(mixed $value): void
    {
        if (call_user_func($this->callable, $value) !== true) {
            throw new Invalid_Header_Exception(sprintf('The "%s" header is invalid.', $this->key), $this->key, $value);
        }
    }
    #[Override]
    public function supported_header(): string
    {
        return $this->key;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return $this->protected_header_only;
    }
}