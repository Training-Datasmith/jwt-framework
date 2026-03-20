<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use function array_key_exists;
use InvalidArgumentException;
use function sprintf;
/**
 * @internal
 */
final readonly class Recipient
{
    public function __construct(private array $header, private ?string $encrypted_key)
    {
    }
    /**
     * Returns the recipient header.
     */
    public function get_header(): array
    {
        return $this->header;
    }
    /**
     * Returns the value of the recipient header parameter with the specified key.
     *
     * @param string $key The key
     *
     * @return mixed|null
     */
    public function get_header_parameter(string $key)
    {
        if (!$this->has_header_parameter($key)) {
            throw new InvalidArgumentException(sprintf('The header "%s" does not exist.', $key));
        }
        return $this->header[$key];
    }
    /**
     * Returns true if the recipient header contains the parameter with the specified key.
     *
     * @param string $key The key
     */
    public function has_header_parameter(string $key): bool
    {
        return array_key_exists($key, $this->header);
    }
    /**
     * Returns the encrypted key.
     */
    public function get_encrypted_key(): ?string
    {
        return $this->encrypted_key;
    }
}