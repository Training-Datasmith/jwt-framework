<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Serializer;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Recipient;
use Override;
final readonly class Json_Flattened_Serializer implements Jwe_Serializer
{
    public const NAME = 'jwe_json_flattened';
    #[Override]
    public function display_name(): string
    {
        return 'JWE JSON Flattened';
    }
    #[Override]
    public function name(): string
    {
        return self::NAME;
    }
    #[Override]
    public function serialize(JWE $jwe, ?int $recipient_index = null): string
    {
        if ($recipient_index === null) {
            $recipient_index = 0;
        }
        $recipient = $jwe->get_recipient($recipient_index);
        $data = ['ciphertext' => Base64url_Safe::encode_unpadded($jwe->get_ciphertext() ?? ''), 'iv' => Base64url_Safe::encode_unpadded($jwe->get_iv() ?? ''), 'tag' => Base64url_Safe::encode_unpadded($jwe->get_tag() ?? '')];
        if ($jwe->get_aad() !== null) {
            $data['aad'] = Base64url_Safe::encode_unpadded($jwe->get_aad());
        }
        if (count($jwe->get_shared_protected_header()) !== 0) {
            $data['protected'] = $jwe->get_encoded_shared_protected_header();
        }
        if (count($jwe->get_shared_header()) !== 0) {
            $data['unprotected'] = $jwe->get_shared_header();
        }
        if (count($recipient->get_header()) !== 0) {
            $data['header'] = $recipient->get_header();
        }
        if ($recipient->get_encrypted_key() !== null) {
            $data['encrypted_key'] = Base64url_Safe::encode_unpadded($recipient->get_encrypted_key());
        }
        return Json_Converter::encode($data);
    }
    #[Override]
    public function unserialize(string $input): JWE
    {
        $data = Json_Converter::decode($input);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        $this->check_data($data);
        $ciphertext = Base64url_Safe::decode_no_padding($data['ciphertext']);
        $iv = Base64url_Safe::decode_no_padding($data['iv']);
        $tag = Base64url_Safe::decode_no_padding($data['tag']);
        $aad = array_key_exists('aad', $data) ? Base64url_Safe::decode_no_padding($data['aad']) : null;
        [$encoded_shared_protected_header, $shared_protected_header, $shared_header] = $this->process_headers($data);
        $encrypted_key = array_key_exists('encrypted_key', $data) ? Base64url_Safe::decode_no_padding($data['encrypted_key']) : null;
        $header = array_key_exists('header', $data) ? $data['header'] : [];
        return new JWE($ciphertext, $iv, $tag, $aad, $shared_header, $shared_protected_header, $encoded_shared_protected_header, [new Recipient($header, $encrypted_key)]);
    }
    private function check_data(?array $data): void
    {
        if ($data === null || !isset($data['ciphertext']) || isset($data['recipients'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }
    }
    private function process_headers(array $data): array
    {
        $encoded_shared_protected_header = array_key_exists('protected', $data) ? $data['protected'] : null;
        $shared_protected_header = $encoded_shared_protected_header ? Json_Converter::decode(Base64url_Safe::decode_no_padding($encoded_shared_protected_header)) : [];
        $shared_header = $data['unprotected'] ?? [];
        return [$encoded_shared_protected_header, $shared_protected_header, $shared_header];
    }
}