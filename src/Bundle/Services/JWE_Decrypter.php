<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jwe_Decryption_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Decryption_Success_Event;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Jwe_Decrypter as BaseJWEDecrypter;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final class Jwe_Decrypter extends Base_Jwe_Decrypter
{
    public function __construct(Algorithm_Manager $algorithm_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($algorithm_manager);
    }
    #[Override]
    public function decrypt_using_key_set(JWE &$jwe, Jwk_Set $jwkset, int $recipient, ?JWK &$jwk = null, ?JWK $sender_key = null): bool
    {
        $success = parent::decrypt_using_key_set($jwe, $jwkset, $recipient, $jwk, $sender_key);
        if ($success) {
            $this->event_dispatcher->dispatch(new Jwe_Decryption_Success_Event($jwe, $jwkset, $jwk, $recipient));
        } else {
            $this->event_dispatcher->dispatch(new Jwe_Decryption_Failure_Event($jwe, $jwkset));
        }
        return $success;
    }
}