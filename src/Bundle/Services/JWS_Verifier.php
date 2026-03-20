<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jws_Verification_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Verification_Success_Event;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Jws_Verifier as BaseJWSVerifier;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final class Jws_Verifier extends Base_Jws_Verifier
{
    public function __construct(Algorithm_Manager $signature_algorithm_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($signature_algorithm_manager);
    }
    #[Override]
    public function verify_with_key_set(JWS $jws, Jwk_Set $jwkset, int $signature_index, ?string $detached_payload = null, ?JWK &$jwk = null): bool
    {
        $success = parent::verify_with_key_set($jws, $jwkset, $signature_index, $detached_payload, $jwk);
        if ($success) {
            $this->event_dispatcher->dispatch(new Jws_Verification_Success_Event($jws, $jwkset, $signature_index, $detached_payload, $jwk));
        } else {
            $this->event_dispatcher->dispatch(new Jws_Verification_Failure_Event($jws, $jwkset, $detached_payload));
        }
        return $success;
    }
}