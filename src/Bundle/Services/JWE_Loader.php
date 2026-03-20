<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jwe_Loading_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Loading_Success_Event;
use Jose\Component\Checker\Header_Checker_Manager;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Jwe_Decrypter;
use Jose\Component\Encryption\Jwe_Loader as BaseJWELoader;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Jwe_Loader extends Base_Jwe_Loader
{
    public function __construct(Jwe_Serializer_Manager $serializer_manager, Jwe_Decrypter $jwe_decrypter, ?Header_Checker_Manager $header_checker_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($serializer_manager, $jwe_decrypter, $header_checker_manager);
    }
    #[Override]
    public function load_and_decrypt_with_key_set(string $token, Jwk_Set $keyset, ?int &$recipient): JWE
    {
        try {
            $jwe = parent::load_and_decrypt_with_key_set($token, $keyset, $recipient);
            $this->event_dispatcher->dispatch(new Jwe_Loading_Success_Event($token, $jwe, $keyset, $recipient));
            return $jwe;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Jwe_Loading_Failure_Event($token, $keyset, $throwable));
            throw $throwable;
        }
    }
}