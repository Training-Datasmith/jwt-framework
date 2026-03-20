<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Serializer;

use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Signature\JWS;
use Override;
final readonly class Json_Flattened_Serializer extends Serializer
{
    public const NAME = 'jws_json_flattened';
    #[Override]
    public function display_name(): string
    {
        return 'JWS JSON Flattened';
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
        $data = [];
        $encoded_payload = $jws->get_encoded_payload();
        if ($encoded_payload !== null && $encoded_payload !== '') {
            $data['payload'] = $encoded_payload;
        }
        $encoded_protected_header = $signature->get_encoded_protected_header();
        if ($encoded_protected_header !== null && $encoded_protected_header !== '') {
            $data['protected'] = $encoded_protected_header;
        }
        $header = $signature->get_header();
        if (count($header) !== 0) {
            $data['header'] = $header;
        }
        $data['signature'] = Base64url_Safe::encode_unpadded($signature->get_signature());
        return Json_Converter::encode($data);
    }
    #[Override]
    public function unserialize(string $input): JWS
    {
        $data = Json_Converter::decode($input);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        if (!isset($data['signature'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        $signature = Base64url_Safe::decode_no_padding($data['signature']);
        if (isset($data['protected'])) {
            $encoded_protected_header = $data['protected'];
            $protected_header = Json_Converter::decode(Base64url_Safe::decode_no_padding($data['protected']));
            if (!is_array($protected_header)) {
                throw new InvalidArgumentException('Bad protected header.');
            }
        } else {
            $encoded_protected_header = null;
            $protected_header = [];
        }
        if (isset($data['header'])) {
            if (!is_array($data['header'])) {
                throw new InvalidArgumentException('Bad header.');
            }
            $header = $data['header'];
        } else {
            $header = [];
        }
        if (isset($data['payload'])) {
            $encoded_payload = $data['payload'];
            $payload = $this->is_payload_encoded($protected_header) ? Base64url_Safe::decode_no_padding($encoded_payload) : $encoded_payload;
        } else {
            $payload = null;
            $encoded_payload = null;
        }
        $jws = new JWS($payload, $encoded_payload, $encoded_payload === null);
        return $jws->add_signature($signature, $protected_header, $encoded_protected_header, $header);
    }
}