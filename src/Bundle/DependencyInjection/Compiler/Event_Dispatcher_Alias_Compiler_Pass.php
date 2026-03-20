<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
final readonly class Event_Dispatcher_Alias_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition('event_dispatcher') || $container->has_alias(Event_Dispatcher_Interface::class)) {
            return;
        }
        $container->set_alias(Event_Dispatcher_Interface::class, 'event_dispatcher');
    }
}