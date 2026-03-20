<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Header_Checked_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Header_Checked_Success_Event;
use Jose\Component\Checker\Header_Checker_Manager as BaseHeaderCheckerManager;
use Jose\Component\Core\JWT;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Header_Checker_Manager extends Base_Header_Checker_Manager
{
    public function __construct(array $checkers, array $token_types, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($checkers, $token_types);
    }
    #[Override]
    public function check(JWT $jwt, int $index, array $mandatory_header_parameters = []): void
    {
        try {
            Base_Header_Checker_Manager::check($jwt, $index, $mandatory_header_parameters);
            $this->event_dispatcher->dispatch(new Header_Checked_Success_Event($jwt, $index, $mandatory_header_parameters));
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Header_Checked_Failure_Event($jwt, $index, $mandatory_header_parameters, $throwable));
            throw $throwable;
        }
    }
}