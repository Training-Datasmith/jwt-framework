<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Serializer;

use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Signature\JWS;
use LogicException;
use Override;
use function sprintf;
use Throwable;
final readonly class Compact_Serializer extends Serializer
{
    public const NAME = 'jws_compact';
    #[Override]
    public function display_name(): string
    {
        return 'JWS Compact';
    }
    #[Override]
    public function name(): string
    {
        return self::NAME;
    }
    #[Override]
    public function serialize(JWS $jws, ?int $signature_index = null): string
    {
        if ($signature_index === null) {
            $signature_index = 0;
        }
        $signature = $jws->get_signature($signature_index);
        if (count($signature->get_header()) !== 0) {
            throw new LogicException('The signature contains unprotected header parameters and cannot be converted into compact JSON.');
        }
        $is_empty_payload = $jws->get_encoded_payload() === null || $jws->get_encoded_payload() === '';
        if (!$is_empty_payload && !$this->is_payload_encoded($signature->get_protected_header())) {
            if (preg_match('/^[\x{20}-\x{2d}|\x{2f}-\x{7e}]*$/u', $jws->get_payload() ?? '') !== 1) {
                throw new LogicException('Unable to convert the JWS with non-encoded payload.');
            }
        }
        return sprintf('%s.%s.%s', $signature->get_encoded_protected_header(), $jws->get_encoded_payload(), Base64url_Safe::encode_unpadded($signature->get_signature()));
    }
    #[Override]
    public function unserialize(string $input): JWS
    {
        $parts = explode('.', $input);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Unsupported input');
        }
        try {
            $encoded_protected_header = $parts[0];
            $protected_header = Json_Converter::decode(Base64url_Safe::decode_no_padding($parts[0]));
            if (!is_array($protected_header)) {
                throw new InvalidArgumentException('Bad protected header.');
            }
            $has_payload = $parts[1] !== '';
            if (!$has_payload) {
                $payload = null;
                $encoded_payload = null;
            } else {
                $encoded_payload = $parts[1];
                $payload = $this->is_payload_encoded($protected_header) ? Base64url_Safe::decode_no_padding($encoded_payload) : $encoded_payload;
            }
            $signature = Base64url_Safe::decode_no_padding($parts[2]);
            $jws = new JWS($payload, $encoded_payload, !$has_payload);
            return $jws->add_signature($signature, $protected_header, $encoded_protected_header);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException('Unsupported input', $throwable->get_code(), $throwable);
        }
    }
}