<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use function array_key_exists;
use function count;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Core\Util\Key_Checker;
use Jose\Component\Signature\Algorithm\Mac_Algorithm;
use Jose\Component\Signature\Algorithm\Signature_Algorithm;
use LogicException;
use RuntimeException;
use function sprintf;
class Jws_Builder
{
    protected ?string $payload = null;
    protected bool $is_payload_detached = false;
    /**
     * @var array<array{
     *     header: array<string, mixed>,
     *     protected_header: array<string, mixed>,
     *     signature_key: JWK,
     *     signature_algorithm: Algorithm
     * }>
     */
    protected array $signatures = [];
    protected ?bool $is_payload_encoded = null;
    public function __construct(private readonly Algorithm_Manager $signature_algorithm_manager)
    {
    }
    /**
     * Returns the algorithm manager associated to the builder.
     */
    public function get_signature_algorithm_manager(): Algorithm_Manager
    {
        return $this->signature_algorithm_manager;
    }
    /**
     * Reset the current data.
     */
    public function create(): self
    {
        $this->payload = null;
        $this->is_payload_detached = false;
        $this->signatures = [];
        $this->is_payload_encoded = null;
        return $this;
    }
    /**
     * Set the payload. This method will return a new JWSBuilder object.
     */
    public function with_payload(string $payload, bool $is_payload_detached = false): self
    {
        $clone = clone $this;
        $clone->payload = $payload;
        $clone->is_payload_detached = $is_payload_detached;
        return $clone;
    }
    /**
     * Adds the information needed to compute the signature. This method will return a new JWSBuilder object.
     *
     * @param array<string, mixed> $protectedHeader
     * @param array<string, mixed> $header
     */
    public function add_signature(JWK $signature_key, array $protected_header, array $header = []): self
    {
        $this->check_b64and_critical_header($protected_header);
        $is_payload_encoded = $this->check_if_payload_is_encoded($protected_header);
        if ($this->is_payload_encoded === null) {
            $this->is_payload_encoded = $is_payload_encoded;
        } elseif ($this->is_payload_encoded !== $is_payload_encoded) {
            throw new InvalidArgumentException('Foreign payload encoding detected.');
        }
        $this->check_duplicated_header_parameters($protected_header, $header);
        Key_Checker::check_key_usage($signature_key, 'signature');
        $algorithm = $this->find_signature_algorithm($signature_key, $protected_header, $header);
        Key_Checker::check_key_algorithm($signature_key, $algorithm->name());
        $clone = clone $this;
        $clone->signatures[] = ['signature_algorithm' => $algorithm, 'signature_key' => $signature_key, 'protected_header' => $protected_header, 'header' => $header];
        return $clone;
    }
    /**
     * Computes all signatures and return the expected JWS object.
     */
    public function build(): JWS
    {
        if ($this->payload === null) {
            throw new RuntimeException('The payload is not set.');
        }
        if (count($this->signatures) === 0) {
            throw new RuntimeException('At least one signature must be set.');
        }
        $encoded_payload = $this->is_payload_encoded === false ? $this->payload : Base64url_Safe::encode_unpadded($this->payload);
        if ($this->is_payload_encoded === false && $this->is_payload_detached === false) {
            mb_detect_encoding($this->payload, 'UTF-8', true) !== false || throw new InvalidArgumentException('The payload must be encoded in UTF-8');
        }
        $jws = new JWS($this->payload, $encoded_payload, $this->is_payload_detached);
        foreach ($this->signatures as $signature) {
            /** @var MacAlgorithm|SignatureAlgorithm $algorithm */
            $algorithm = $signature['signature_algorithm'];
            /** @var JWK $signatureKey */
            $signature_key = $signature['signature_key'];
            /** @var array<string, mixed> $protectedHeader */
            $protected_header = $signature['protected_header'];
            /** @var array<string, mixed> $header */
            $header = $signature['header'];
            $encoded_protected_header = count($protected_header) === 0 ? null : Base64url_Safe::encode_unpadded(Json_Converter::encode($protected_header));
            $input = sprintf('%s.%s', $encoded_protected_header, $encoded_payload);
            if ($algorithm instanceof Signature_Algorithm) {
                $s = $algorithm->sign($signature_key, $input);
            } else {
                $s = $algorithm->hash($signature_key, $input);
            }
            $jws = $jws->add_signature($s, $protected_header, $encoded_protected_header, $header);
        }
        return $jws;
    }
    /**
     * @param array<string, mixed> $protectedHeader
     */
    private function check_if_payload_is_encoded(array $protected_header): bool
    {
        return !array_key_exists('b64', $protected_header) || $protected_header['b64'] === true;
    }
    /**
     * @param array<string, mixed> $protectedHeader
     */
    private function check_b64and_critical_header(array $protected_header): void
    {
        if (!array_key_exists('b64', $protected_header)) {
            return;
        }
        if (!array_key_exists('crit', $protected_header)) {
            throw new LogicException('The protected header parameter "crit" is mandatory when protected header parameter "b64" is set.');
        }
        if (!is_array($protected_header['crit'])) {
            throw new LogicException('The protected header parameter "crit" must be an array.');
        }
        if (!in_array('b64', $protected_header['crit'], true)) {
            throw new LogicException('The protected header parameter "crit" must contain "b64" when protected header parameter "b64" is set.');
        }
    }
    /**
     * @param array<string, mixed> $protectedHeader
     * @param array<string, mixed> $header
     * @return MacAlgorithm|SignatureAlgorithm
     */
    private function find_signature_algorithm(JWK $key, array $protected_header, array $header): Algorithm
    {
        $complete_header = [...$header, ...$protected_header];
        $alg = $complete_header['alg'] ?? null;
        if (!is_string($alg)) {
            throw new InvalidArgumentException('No "alg" parameter set in the header.');
        }
        $key_alg = $key->has('alg') ? $key->get('alg') : null;
        if (is_string($key_alg) && $key_alg !== $alg) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not allowed with this key.', $alg));
        }
        $algorithm = $this->signature_algorithm_manager->get($alg);
        if (!$algorithm instanceof Signature_Algorithm && !$algorithm instanceof Mac_Algorithm) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not supported.', $alg));
        }
        return $algorithm;
    }
    /**
     * @param array<string, mixed> $header1
     * @param array<string, mixed> $header2
     */
    private function check_duplicated_header_parameters(array $header1, array $header2): void
    {
        $inter = array_intersect_key($header1, $header2);
        if (count($inter) !== 0) {
            throw new InvalidArgumentException(sprintf('The header contains duplicated entries: %s.', implode(', ', array_keys($inter))));
        }
    }
}