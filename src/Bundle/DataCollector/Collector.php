<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Throwable;
interface Collector
{
    /**
     * @param array<string, mixed> $data
     */
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void;
}