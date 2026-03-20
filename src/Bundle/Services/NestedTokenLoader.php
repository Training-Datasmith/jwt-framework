<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Nested_Token_Loading_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Nested_Token_Loading_Success_Event;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\Jwe_Loader;
use Jose\Component\Nested_Token\Nested_Token_Loader as BaseNestedTokenLoader;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Jws_Loader;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Nested_Token_Loader extends Base_Nested_Token_Loader
{
    public function __construct(Jwe_Loader $jwe_loader, Jws_Loader $jws_loader, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($jwe_loader, $jws_loader);
    }
    #[Override]
    public function load(string $token, Jwk_Set $encryption_key_set, Jwk_Set $signature_key_set, ?int &$signature = null): JWS
    {
        try {
            $jws = parent::load($token, $encryption_key_set, $signature_key_set, $signature);
            $this->event_dispatcher->dispatch(new Nested_Token_Loading_Success_Event($token, $jws, $signature_key_set, $encryption_key_set, $signature));
            return $jws;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Nested_Token_Loading_Failure_Event($token, $signature_key_set, $encryption_key_set, $throwable));
            throw $throwable;
        }
    }
}