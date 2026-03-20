<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use function count;
use InvalidArgumentException;
use Jose\Component\Core\JWT;
use Override;
/**
 * @see \Jose\Tests\Component\Signature\JWSTest
 */
class JWS implements JWT
{
    /**
     * @var Signature[]
     */
    private array $signatures = [];
    public function __construct(private readonly ?string $payload, private readonly ?string $encoded_payload = null, private readonly bool $is_payload_detached = false)
    {
    }
    #[Override]
    public function get_payload(): ?string
    {
        return $this->payload;
    }
    /**
     * Returns true if the payload is detached.
     */
    public function is_payload_detached(): bool
    {
        return $this->is_payload_detached;
    }
    /**
     * Returns the Base64Url encoded payload. If the payload is detached, this method returns null.
     */
    public function get_encoded_payload(): ?string
    {
        if ($this->is_payload_detached() === true) {
            return null;
        }
        return $this->encoded_payload;
    }
    /**
     * Returns the signatures associated with the JWS.
     *
     * @return Signature[]
     */
    public function get_signatures(): array
    {
        return $this->signatures;
    }
    /**
     * Returns the signature at the given index.
     */
    public function get_signature(int $id): Signature
    {
        if (isset($this->signatures[$id])) {
            return $this->signatures[$id];
        }
        throw new InvalidArgumentException('The signature does not exist.');
    }
    /**
     * This method adds a signature to the JWS object. Its returns a new JWS object.
     *
     * @internal
     *
     * @param array{alg?: string, string?: mixed} $protectedHeader
     * @param array{alg?: string, string?: mixed} $header
     */
    public function add_signature(string $signature, array $protected_header, ?string $encoded_protected_header, array $header = []): self
    {
        $jws = clone $this;
        $jws->signatures[] = new Signature($signature, $protected_header, $encoded_protected_header, $header);
        return $jws;
    }
    /**
     * Returns the number of signature associated with the JWS.
     */
    public function count_signatures(): int
    {
        return count($this->signatures);
    }
    /**
     * This method splits the JWS into a list of JWSs. It is only useful when the JWS contains more than one signature
     * (JSON General Serialization).
     *
     * @return JWS[]
     */
    public function split(): array
    {
        $result = [];
        foreach ($this->signatures as $signature) {
            $jws = new self($this->payload, $this->encoded_payload, $this->is_payload_detached);
            $jws = $jws->add_signature($signature->get_signature(), $signature->get_protected_header(), $signature->get_encoded_protected_header(), $signature->get_header());
            $result[] = $jws;
        }
        return $result;
    }
}