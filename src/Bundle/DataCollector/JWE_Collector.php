<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Jose\Bundle\Jose_Framework\Event\Jwe_Built_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Built_Success_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Decryption_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Decryption_Success_Event;
use Jose\Component\Encryption\Jwe_Builder;
use Jose\Component\Encryption\Jwe_Decrypter;
use Jose\Component\Encryption\Jwe_Loader;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Override;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Var_Dumper\Cloner\Data;
use Symfony\Component\Var_Dumper\Cloner\Var_Cloner;
use Throwable;
final class Jwe_Collector implements Collector, Event_Subscriber_Interface
{
    /**
     * @var array<Data>
     */
    private array $jwe_decryption_successes = [];
    /**
     * @var array<Data>
     */
    private array $jwe_decryption_failures = [];
    /**
     * @var array<Data>
     */
    private array $jwe_built_successes = [];
    /**
     * @var array<Data>
     */
    private array $jwe_built_failures = [];
    /**
     * @var array<JWEBuilder>
     */
    private array $jwe_builders = [];
    /**
     * @var array<JWEDecrypter>
     */
    private array $jwe_decrypters = [];
    /**
     * @var array<JWELoader>
     */
    private array $jwe_loaders = [];
    public function __construct(private readonly ?Jwe_Serializer_Manager_Factory $jwe_serializer_manager_factory = null)
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->collect_supported_jwe_serializations($data);
        $this->collect_supported_jwe_builders($data);
        $this->collect_supported_jwe_decrypters($data);
        $this->collect_supported_jwe_loaders($data);
        $this->collect_events($data);
    }
    public function add_jwe_builder(string $id, Jwe_Builder $jwe_builder): void
    {
        $this->jwe_builders[$id] = $jwe_builder;
    }
    public function add_jwe_decrypter(string $id, Jwe_Decrypter $jwe_decrypter): void
    {
        $this->jwe_decrypters[$id] = $jwe_decrypter;
    }
    public function add_jwe_loader(string $id, Jwe_Loader $jwe_loader): void
    {
        $this->jwe_loaders[$id] = $jwe_loader;
    }
    #[Override]
    public static function get_subscribed_events(): array
    {
        return [Jwe_Decryption_Success_Event::class => ['catchJweDecryptionSuccess'], Jwe_Decryption_Failure_Event::class => ['catchJweDecryptionFailure'], Jwe_Built_Success_Event::class => ['catchJweBuiltSuccess'], Jwe_Built_Failure_Event::class => ['catchJweBuiltFailure']];
    }
    public function catch_jwe_decryption_success(Jwe_Decryption_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jwe_decryption_successes[] = $cloner->clone_var($event);
    }
    public function catch_jwe_decryption_failure(Jwe_Decryption_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jwe_decryption_failures[] = $cloner->clone_var($event);
    }
    public function catch_jwe_built_success(Jwe_Built_Success_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jwe_built_successes[] = $cloner->clone_var($event);
    }
    public function catch_jwe_built_failure(Jwe_Built_Failure_Event $event): void
    {
        $cloner = new Var_Cloner();
        $this->jwe_built_failures[] = $cloner->clone_var($event);
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jwe_serializations(array &$data): void
    {
        $data['jwe']['jwe_serialization'] = [];
        if ($this->jwe_serializer_manager_factory === null) {
            return;
        }
        $serializers = $this->jwe_serializer_manager_factory->all();
        foreach ($serializers as $serializer) {
            $data['jwe']['jwe_serialization'][$serializer->name()] = $serializer->display_name();
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jwe_builders(array &$data): void
    {
        $data['jwe']['jwe_builders'] = [];
        foreach ($this->jwe_builders as $id => $jwe_builder) {
            $data['jwe']['jwe_builders'][$id] = ['encryption_algorithms' => $jwe_builder->get_key_encryption_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jwe_decrypters(array &$data): void
    {
        $data['jwe']['jwe_decrypters'] = [];
        foreach ($this->jwe_decrypters as $id => $jwe_decrypter) {
            $data['jwe']['jwe_decrypters'][$id] = ['encryption_algorithms' => $jwe_decrypter->get_key_encryption_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_supported_jwe_loaders(array &$data): void
    {
        $data['jwe']['jwe_loaders'] = [];
        foreach ($this->jwe_loaders as $id => $jwe_loader) {
            $data['jwe']['jwe_loaders'][$id] = ['serializers' => $jwe_loader->get_serializer_manager()->names(), 'encryption_algorithms' => $jwe_loader->get_jwe_decrypter()->get_key_encryption_algorithm_manager()->list()];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_events(array &$data): void
    {
        $data['jwe']['events'] = ['decryption_success' => $this->jwe_decryption_successes, 'decryption_failure' => $this->jwe_decryption_failures, 'built_success' => $this->jwe_built_successes, 'built_failure' => $this->jwe_built_failures];
    }
}