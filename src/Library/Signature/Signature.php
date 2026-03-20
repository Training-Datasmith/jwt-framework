<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use function array_key_exists;
use InvalidArgumentException;
use function sprintf;
class Signature
{
    private readonly ?string $encoded_protected_header;
    /**
     * @var array<string, mixed>
     */
    private readonly array $protected_header;
    /**
     * @param array{alg?: string, string?: mixed} $protectedHeader
     * @param array{alg?: string, string?: mixed} $header
     */
    public function __construct(private readonly string $signature, array $protected_header, ?string $encoded_protected_header, private readonly array $header)
    {
        $this->protected_header = $encoded_protected_header === null ? [] : $protected_header;
        $this->encoded_protected_header = $encoded_protected_header;
    }
    /**
     * The protected header associated with the signature.
     *
     * @return array<string, mixed>
     */
    public function get_protected_header(): array
    {
        return $this->protected_header;
    }
    /**
     * The unprotected header associated with the signature.
     *
     * @return array<string, mixed>
     */
    public function get_header(): array
    {
        return $this->header;
    }
    /**
     * The protected header associated with the signature.
     */
    public function get_encoded_protected_header(): ?string
    {
        return $this->encoded_protected_header;
    }
    /**
     * Returns the value of the protected header of the specified key.
     *
     * @param string $key The key
     *
     * @return mixed|null Header value
     */
    public function get_protected_header_parameter(string $key)
    {
        if ($this->has_protected_header_parameter($key)) {
            return $this->get_protected_header()[$key];
        }
        throw new InvalidArgumentException(sprintf('The protected header "%s" does not exist', $key));
    }
    /**
     * Returns true if the protected header has the given parameter.
     *
     * @param string $key The key
     */
    public function has_protected_header_parameter(string $key): bool
    {
        return array_key_exists($key, $this->get_protected_header());
    }
    /**
     * Returns the value of the unprotected header of the specified key.
     *
     * @param string $key The key
     *
     * @return mixed|null Header value
     */
    public function get_header_parameter(string $key)
    {
        if (array_key_exists($key, $this->header)) {
            return $this->header[$key];
        }
        throw new InvalidArgumentException(sprintf('The header "%s" does not exist', $key));
    }
    /**
     * Returns true if the unprotected header has the given parameter.
     *
     * @param string $key The key
     */
    public function has_header_parameter(string $key): bool
    {
        return array_key_exists($key, $this->header);
    }
    /**
     * Returns the value of the signature.
     */
    public function get_signature(): string
    {
        return $this->signature;
    }
}