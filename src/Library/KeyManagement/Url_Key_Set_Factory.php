<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management;

use function assert;
use Psr\Cache\Cache_Item_Pool_Interface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\Null_Adapter;
use Symfony\Contracts\Http_Client\Http_Client_Interface;
/**
 * @see \Jose\Tests\Component\KeyManagement\UrlKeySetFactoryTest
 */
abstract class Url_Key_Set_Factory
{
    private Cache_Item_Pool_Interface $cache_item_pool;
    private int $expires_after = 3600;
    public function __construct(private readonly Http_Client_Interface $client)
    {
        $this->cache_item_pool = new Null_Adapter();
    }
    /**
     * @deprecated since 4.1 and will be removed in 5.0. Please use the Http Client to cache the responses instead.
     */
    public function enabled_cache(Cache_Item_Pool_Interface $cache_item_pool, int $expires_after = 3600): void
    {
        $this->cache_item_pool = $cache_item_pool;
        $this->expires_after = $expires_after;
    }
    /**
     * @param array<string, string|string[]> $header
     */
    protected function get_content(string $url, array $header = []): string
    {
        $cache_key = hash('xxh128', $url);
        $item = $this->cache_item_pool->get_item($cache_key);
        if ($item->is_hit()) {
            return $item->get();
        }
        $content = $this->client instanceof Http_Client_Interface ? $this->send_symfony_request($url, $header) : $this->send_psr_request($url, $header);
        $item = $this->cache_item_pool->get_item($cache_key);
        $item->expires_after($this->expires_after);
        $item->set($content);
        $this->cache_item_pool->save($item);
        return $content;
    }
    /**
     * @param array<string, string|string[]> $header
     */
    private function send_symfony_request(string $url, array $header = []): string
    {
        assert($this->client instanceof Http_Client_Interface);
        $response = $this->client->request('GET', $url, ['headers' => $header]);
        if ($response->get_status_code() >= 400) {
            throw new RuntimeException('Unable to get the key set.', $response->get_status_code());
        }
        return $response->get_content();
    }
}