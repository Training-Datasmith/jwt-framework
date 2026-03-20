<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management;

use function is_array;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use RuntimeException;
class Jku_Factory extends Url_Key_Set_Factory
{
    /**
     * This method will try to fetch the url a retrieve the key set. Throws an exception in case of failure.
     */
    public function load_from_url(string $url, array $header = []): Jwk_Set
    {
        $content = $this->get_content($url, $header);
        $data = Json_Converter::decode($content);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid content.');
        }
        return Jwk_Set::create_from_key_data($data);
    }
}