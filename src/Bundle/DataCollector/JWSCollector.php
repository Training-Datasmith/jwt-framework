<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Jose\Bundle\Jose_Framework\Event\Jws_Built_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Built_Success_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Verification_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Verification_Success_Event;
use Jose\Component\Signature\Jws_Builder;
use Jose\Component\Signature\Jws_Loader;
use Jose\Component\Signature\Jws_Verifier;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
use Override;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Var_Dumper\Cloner\Data;
use Symfony\Component\Var_Dumper\Cloner\Var_Cloner;
use Throwable;
final class Jws_Collector implements Collector, Event_Subscriber_Interface
{
    /**
     * @var array<JWSBuilder>
     */
    private array $jws_builders = [];
    /**
     * @var JWSVerifier[]
     */
    private array $jws_verifiers = [];
    /**
     * @var JWSLoader[]
     */
    private array $jws_loaders = [];
    /**
     * @var array<Data>
     */
    private array $jws_verification_successes = [];
    /**
     * @var array<Data>
     */
    private array $jws_verification_failures = [];
    /**
     * @var array<Data>
     */
    private array $jws_built_successes = [];
    /**
     * @var array<Data>
     */
    private array $jws_built_failures = [];
    public function __construct(private readonly ?Jws_Serializer_Manager_Factory $jws_serializer_manager_factory = null)
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->collect_supported_jws_serializations($data);
        $this->collect_supported_jws_builders($data);
        $this->collect_supported_jws_verifiers($data);
        $this->collect_supported_jws_loaders($data);
        $this->collect_events($data);
    }
    public function add_jws_builder(string $id, Jws_Builder $jws_builder): void
    {
        $this->jws_builders[$id] = $jws_builder;
    }
    public function add_jws_verifier(string $id, Jws_Verifier $jws_verifier): void
    {
        $this->jws_verifiers[$id] = $jws_verifier;
    }
    public function add_jws_loader(string $id, Jws_Loader $jws_loader): void
    {
        $this->jws_loaders[$id] = $jws_loader;
    }
    #[Override]
    public static function get_subscribed_events(): array
    {
        return [Jws_Verification_Success_Event::class => ['catchJwsVerificationSuccess'], Jws_Verification_Failure_Event::class => ['catchJwsVerificationFailure'], Jws_Built_Success_Event::class => ['catchJwsBuiltSuccess'], Jws_Built_Failure_Event::class => ['catchJwsBuiltFailure']];
    }
    public function catch_jws_verification_success(Jws_Verification_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jws_verification_successes[] = $cloner->clone_var($event);
    }
    public function catch_jws_verification_failure(Jws_Verification_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jws_verification_failures[] = $cloner->clone_var($event);
    }
    public function catch_jws_built_success(Jws_Built_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jws_built_successes[] = $cloner->clone_var($event);
    }
    public function catch_jws_built_failure(Jws_Built_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jws_built_failures[] = $cloner->clone_var($event);
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jws_serializations(array &$data): void
    {
        $data['jws']['jws_serialization'] = [];
        if ($this->jws_serializer_manager_factory === null) {
            return;
        }
        $serializers = $this->jws_serializer_manager_factory->all();
        foreach ($serializers as $serializer) {
            $data['jws']['jws_serialization'][$serializer->name()] = $serializer->display_name();
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jws_builders(array &$data): void
    {
        $data['jws']['jws_builders'] = [];
        foreach ($this->jws_builders as $id => $jws_builder) {
            $data['jws']['jws_builders'][$id] = ['signature_algorithms' => $jws_builder->get_signature_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jws_verifiers(array &$data): void
    {
        $data['jws']['jws_verifiers'] = [];
        foreach ($this->jws_verifiers as $id => $jws_verifier) {
            $data['jws']['jws_verifiers'][$id] = ['signature_algorithms' => $jws_verifier->get_signature_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jws_loaders(array &$data): void
    {
        $data['jws']['jws_loaders'] = [];
        foreach ($this->jws_loaders as $id => $jws_loader) {
            $data['jws']['jws_loaders'][$id] = ['serializers' => $jws_loader->get_serializer_manager()->list(), 'signature_algorithms' => $jws_loader->get_jws_verifier()->get_signature_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_events(array &$data): void
    {
        $data['jws']['events'] = ['verification_success' => $this->jws_verification_successes, 'verification_failure' => $this->jws_verification_failures, 'built_success' => $this->jws_built_successes, 'built_failure' => $this->jws_built_failures];
    }
}