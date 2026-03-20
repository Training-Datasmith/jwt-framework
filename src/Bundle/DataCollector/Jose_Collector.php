<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Override;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Data_Collector\Data_Collector;
use Symfony\Component\Var_Dumper\Cloner\Data;
use Throwable;
final class Jose_Collector extends Data_Collector
{
    /**
     * @var Collector[]
     */
    private array $collectors = [];
    #[Override]
    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        foreach ($this->collectors as $collector) {
            $collector->collect($this->data, $request, $response, $exception);
        }
    }
    public function add(Collector $collector): void
    {
        $this->collectors[] = $collector;
    }
    #[Override]
    public function get_name(): string
    {
        return 'jose_collector';
    }
    /**
     * @return array<string, mixed>|Data
     */
    public function get_data(): array|Data
    {
        return $this->data;
    }
    #[Override]
    public function reset(): void
    {
        $this->data = [];
    }
}