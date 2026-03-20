<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Jose\Bundle\Jose_Framework\Event\Claim_Checked_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Claim_Checked_Success_Event;
use Jose\Bundle\Jose_Framework\Event\Header_Checked_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Header_Checked_Success_Event;
use Jose\Bundle\Jose_Framework\Services\Claim_Checker_Manager;
use Jose\Bundle\Jose_Framework\Services\Claim_Checker_Manager_Factory;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager_Factory;
use Override;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Var_Dumper\Cloner\Data;
use Symfony\Component\Var_Dumper\Cloner\Var_Cloner;
use Throwable;
final class Checker_Collector implements Collector, Event_Subscriber_Interface
{
    /**
     * @var array<Data>
     */
    private array $header_checked_successes = [];
    /**
     * @var array<Data>
     */
    private array $header_checked_failures = [];
    /**
     * @var array<Data>
     */
    private array $claim_checked_successes = [];
    /**
     * @var array<Data>
     */
    private array $claim_checked_failures = [];
    /**
     * @var array<HeaderCheckerManager>
     */
    private array $header_checker_managers = [];
    /**
     * @var array<ClaimCheckerManager>
     */
    private array $claim_checker_managers = [];
    public function __construct(private readonly ?Claim_Checker_Manager_Factory $claim_checker_manager_factory = null, private readonly ?Header_Checker_Manager_Factory $header_checker_manager_factory = null)
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->collect_header_checker_managers($data);
        $this->collect_supported_header_checkers($data);
        $this->collect_claim_checker_managers($data);
        $this->collect_supported_claim_checkers($data);
        $this->collect_events($data);
    }
    public function add_header_checker_manager(string $id, Header_Checker_Manager $header_checker_manager): void
    {
        $this->header_checker_managers[$id] = $header_checker_manager;
    }
    public function add_claim_checker_manager(string $id, Claim_Checker_Manager $claim_checker_manager): void
    {
        $this->claim_checker_managers[$id] = $claim_checker_manager;
    }
    #[Override]
    public static function get_subscribed_events(): array
    {
        return [Header_Checked_Success_Event::class => ['catchHeaderCheckSuccess'], Header_Checked_Failure_Event::class => ['catchHeaderCheckFailure'], Claim_Checked_Success_Event::class => ['catchClaimCheckSuccess'], Claim_Checked_Failure_Event::class => ['catchClaimCheckFailure']];
    }
    public function catch_header_check_success(Header_Checked_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->header_checked_successes[] = $cloner->clone_var($event);
    }
    public function catch_header_check_failure(Header_Checked_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->header_checked_failures[] = $cloner->clone_var($event);
    }
    public function catch_claim_check_success(Claim_Checked_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->claim_checked_successes[] = $cloner->clone_var($event);
    }
    public function catch_claim_check_failure(Claim_Checked_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->claim_checked_failures[] = $cloner->clone_var($event);
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_header_checker_managers(array &$data): void
    {
        $data['checker']['header_checker_managers'] = [];
        foreach ($this->header_checker_managers as $id => $checker_manager) {
            $data['checker']['header_checker_managers'][$id] = [];
            foreach ($checker_manager->get_checkers() as $checker) {
                $data['checker']['header_checker_managers'][$id][] = ['header' => $checker->supported_header(), 'protected' => $checker->protected_header_only()];
            }
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_header_checkers(array &$data): void
    {
        $data['checker']['header_checkers'] = [];
        if ($this->header_checker_manager_factory !== null) {
            $aliases = $this->header_checker_manager_factory->all();
            foreach ($aliases as $alias => $checker) {
                $data['checker']['header_checkers'][$alias] = ['header' => $checker->supported_header(), 'protected' => $checker->protected_header_only()];
            }
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_claim_checker_managers(array &$data): void
    {
        $data['checker']['claim_checker_managers'] = [];
        foreach ($this->claim_checker_managers as $id => $checker_manager) {
            $data['checker']['claim_checker_managers'][$id] = [];
            foreach ($checker_manager->get_checkers() as $checker) {
                $data['checker']['claim_checker_managers'][$id][] = ['claim' => $checker->supported_claim()];
            }
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_claim_checkers(array &$data): void
    {
        $data['checker']['claim_checkers'] = [];
        if ($this->claim_checker_manager_factory !== null) {
            $aliases = $this->claim_checker_manager_factory->all();
            foreach ($aliases as $alias => $checker) {
                $data['checker']['claim_checkers'][$alias] = ['claim' => $checker->supported_claim()];
            }
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_events(array &$data): void
    {
        $data['checker']['events'] = ['header_check_success' => $this->header_checked_successes, 'header_check_failure' => $this->header_checked_failures, 'claim_check_success' => $this->claim_checked_successes, 'claim_check_failure' => $this->claim_checked_failures];
    }
}