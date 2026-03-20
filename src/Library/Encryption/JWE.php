<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use Jose\Component\Core\JWT;
use Override;
use function sprintf;
class JWE implements JWT
{
    private ?string $payload = null;
    public function __construct(private readonly ?string $ciphertext, private readonly string $iv, private readonly string $tag, private readonly ?string $aad = null, private readonly array $shared_header = [], private readonly array $shared_protected_header = [], private readonly ?string $encoded_shared_protected_header = null, private readonly array $recipients = [])
    {
    }
    #[Override]
    public function get_payload(): ?string
    {
        return $this->payload;
    }
    /**
     * Set the payload. This method is immutable and a new object will be returned.
     */
    public function with_payload(string $payload): self
    {
        $clone = clone $this;
        $clone->payload = $payload;
        return $clone;
    }
    /**
     * Returns the number of recipients associated with the JWS.
     */
    public function count_recipients(): int
    {
        return count($this->recipients);
    }
    /**
     * Returns true is the JWE has already been encrypted.
     */
    public function is_encrypted(): bool
    {
        return $this->get_ciphertext() !== null;
    }
    /**
     * Returns the recipients associated with the JWS.
     *
     * @return Recipient[]
     */
    public function get_recipients(): array
    {
        return $this->recipients;
    }
    /**
     * Returns the recipient object at the given index.
     */
    public function get_recipient(int $id): Recipient
    {
        if (!isset($this->recipients[$id])) {
            throw new InvalidArgumentException('The recipient does not exist.');
        }
        return $this->recipients[$id];
    }
    /**
     * Returns the ciphertext. This method will return null is the JWE has not yet been encrypted.
     *
     * @return string|null The ciphertext
     */
    public function get_ciphertext(): ?string
    {
        return $this->ciphertext;
    }
    /**
     * Returns the Additional Authentication Data if available.
     */
    public function get_aad(): ?string
    {
        return $this->aad;
    }
    /**
     * Returns the Initialization Vector if available.
     */
    public function get_iv(): ?string
    {
        return $this->iv;
    }
    /**
     * Returns the tag if available.
     */
    public function get_tag(): ?string
    {
        return $this->tag;
    }
    /**
     * Returns the encoded shared protected header.
     */
    public function get_encoded_shared_protected_header(): string
    {
        return $this->encoded_shared_protected_header ?? '';
    }
    /**
     * Returns the shared protected header.
     */
    public function get_shared_protected_header(): array
    {
        return $this->shared_protected_header;
    }
    /**
     * Returns the shared protected header parameter identified by the given key. Throws an exception is the the
     * parameter is not available.
     *
     * @param string $key The key
     *
     * @return mixed|null
     */
    public function get_shared_protected_header_parameter(string $key)
    {
        if (!$this->has_shared_protected_header_parameter($key)) {
            throw new InvalidArgumentException(sprintf('The shared protected header "%s" does not exist.', $key));
        }
        return $this->shared_protected_header[$key];
    }
    /**
     * Returns true if the shared protected header has the parameter identified by the given key.
     *
     * @param string $key The key
     */
    public function has_shared_protected_header_parameter(string $key): bool
    {
        return array_key_exists($key, $this->shared_protected_header);
    }
    /**
     * Returns the shared header.
     */
    public function get_shared_header(): array
    {
        return $this->shared_header;
    }
    /**
     * Returns the shared header parameter identified by the given key. Throws an exception is the the parameter is not
     * available.
     *
     * @param string $key The key
     *
     * @return mixed|null
     */
    public function get_shared_header_parameter(string $key)
    {
        if (!$this->has_shared_header_parameter($key)) {
            throw new InvalidArgumentException(sprintf('The shared header "%s" does not exist.', $key));
        }
        return $this->shared_header[$key];
    }
    /**
     * Returns true if the shared header has the parameter identified by the given key.
     *
     * @param string $key The key
     */
    public function has_shared_header_parameter(string $key): bool
    {
        return array_key_exists($key, $this->shared_header);
    }
    /**
     * This method splits the JWE into a list of JWEs. It is only useful when the JWE contains more than one recipient
     * (JSON General Serialization).
     *
     * @return JWE[]
     */
    public function split(): array
    {
        $result = [];
        foreach ($this->recipients as $recipient) {
            $result[] = new self($this->ciphertext, $this->iv, $this->tag, $this->aad, $this->shared_header, $this->shared_protected_header, $this->encoded_shared_protected_header, [$recipient]);
        }
        return $result;
    }
}