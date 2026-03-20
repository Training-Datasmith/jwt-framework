<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jws_Loading_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Loading_Success_Event;
use Jose\Component\Checker\Header_Checker_Manager;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Jws_Loader as BaseJWSLoader;
use Jose\Component\Signature\Jws_Verifier;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Jws_Loader extends Base_Jws_Loader
{
    public function __construct(Jws_Serializer_Manager $serializer_manager, Jws_Verifier $jws_verifier, ?Header_Checker_Manager $header_checker_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($serializer_manager, $jws_verifier, $header_checker_manager);
    }
    #[Override]
    public function load_and_verify_with_key_set(string $token, Jwk_Set $keyset, ?int &$signature, ?string $payload = null): JWS
    {
        try {
            $jws = parent::load_and_verify_with_key_set($token, $keyset, $signature, $payload);
            $this->event_dispatcher->dispatch(new Jws_Loading_Success_Event($token, $jws, $keyset, $signature));
            return $jws;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Jws_Loading_Failure_Event($token, $keyset, $throwable));
            throw $throwable;
        }
    }
}