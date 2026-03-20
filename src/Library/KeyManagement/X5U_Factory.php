<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management;

use function assert;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Key_Management\Key_Converter\Key_Converter;
use RuntimeException;
class X5u_Factory extends Url_Key_Set_Factory
{
    /**
     * This method will try to fetch the url a retrieve the key set. Throws an exception in case of failure.
     *
     * @param array<string, string|string[]> $header
     */
    public function load_from_url(string $url, array $header = []): Jwk_Set
    {
        $content = $this->get_content($url, $header);
        $data = Json_Converter::decode($content);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid content.');
        }
        $keys = [];
        foreach ($data as $kid => $cert) {
            assert(is_string($cert), 'Invalid content.');
            if (!str_contains($cert, '-----BEGIN CERTIFICATE-----')) {
                $cert = '-----BEGIN CERTIFICATE-----' . "\n" . $cert . "\n" . '-----END CERTIFICATE-----';
            }
            $jwk = Key_Converter::load_key_from_certificate($cert);
            if (is_string($kid)) {
                $jwk['kid'] = $kid;
                $keys[$kid] = new JWK($jwk);
            } else {
                $keys[] = new JWK($jwk);
            }
        }
        return new Jwk_Set($keys);
    }
}