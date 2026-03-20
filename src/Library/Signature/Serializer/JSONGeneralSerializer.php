<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Serializer;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Signature\JWS;
use LogicException;
use Override;
final readonly class Json_General_Serializer extends Serializer
{
    public const NAME = 'jws_json_general';
    #[Override]
    public function display_name(): string
    {
        return 'JWS JSON General';
    }
    #[Override]
    public function name(): string
    {
        return self::NAME;
    }
    #[Override]
    public function serialize(JWS $jws, ?int $signature_index = null): string
    {
        if ($jws->count_signatures() === 0) {
            throw new LogicException('No signature.');
        }
        $data = [];
        $this->check_payload_encoding($jws);
        if ($jws->is_payload_detached() === false) {
            $data['payload'] = $jws->get_encoded_payload();
        }
        $data['signatures'] = [];
        foreach ($jws->get_signatures() as $signature) {
            $tmp = ['signature' => Base64url_Safe::encode_unpadded($signature->get_signature())];
            $values = ['protected' => $signature->get_encoded_protected_header(), 'header' => $signature->get_header()];
            foreach ($values as $key => $value) {
                if (is_string($value) && $value !== '' || is_array($value) && count($value) !== 0) {
                    $tmp[$key] = $value;
                }
            }
            $data['signatures'][] = $tmp;
        }
        return Json_Converter::encode($data);
    }
    #[Override]
    public function unserialize(string $input): JWS
    {
        $data = Json_Converter::decode($input);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        if (!isset($data['signatures'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        $is_payload_encoded = null;
        $raw_payload = $data['payload'] ?? null;
        $signatures = [];
        foreach ($data['signatures'] as $signature) {
            if (!isset($signature['signature'])) {
                throw new InvalidArgumentException('Unsupported input.');
            }
            [$encoded_protected_header, $protected_header, $header] = $this->process_headers($signature);
            $signatures[] = ['signature' => Base64url_Safe::decode_no_padding($signature['signature']), 'protected' => $protected_header, 'encoded_protected' => $encoded_protected_header, 'header' => $header];
            $is_payload_encoded = $this->process_is_payload_encoded($is_payload_encoded, $protected_header);
        }
        $payload = $this->process_payload($raw_payload, $is_payload_encoded);
        $jws = new JWS($payload, $raw_payload);
        foreach ($signatures as $signature) {
            $jws = $jws->add_signature($signature['signature'], $signature['protected'], $signature['encoded_protected'], $signature['header']);
        }
        return $jws;
    }
    /**
     * @param array<string, mixed> $protectedHeader
     */
    private function process_is_payload_encoded(?bool $is_payload_encoded, array $protected_header): bool
    {
        if ($is_payload_encoded === null) {
            return $this->is_payload_encoded($protected_header);
        }
        if ($this->is_payload_encoded($protected_header) !== $is_payload_encoded) {
            throw new InvalidArgumentException('Foreign payload encoding detected.');
        }
        return $is_payload_encoded;
    }
    /**
     * @param array{protected?: string, header?: array<string, mixed>} $signature
     * @return array<mixed>
     */
    private function process_headers(array $signature): array
    {
        $encoded_protected_header = $signature['protected'] ?? null;
        $protected_header = $encoded_protected_header === null ? [] : Json_Converter::decode(Base64url_Safe::decode_no_padding($encoded_protected_header));
        $header = array_key_exists('header', $signature) ? $signature['header'] : [];
        return [$encoded_protected_header, $protected_header, $header];
    }
    private function process_payload(?string $raw_payload, ?bool $is_payload_encoded): ?string
    {
        if ($raw_payload === null) {
            return null;
        }
        return $is_payload_encoded === false ? $raw_payload : Base64url_Safe::decode_no_padding($raw_payload);
    }
    private function check_payload_encoding(JWS $jws): void
    {
        if ($jws->is_payload_detached()) {
            return;
        }
        $is_encoded = null;
        foreach ($jws->get_signatures() as $signature) {
            if ($is_encoded === null) {
                $is_encoded = $this->is_payload_encoded($signature->get_protected_header());
            }
            if ($is_encoded !== $this->is_payload_encoded($signature->get_protected_header())) {
                throw new LogicException('Foreign payload encoding detected.');
            }
        }
    }
}