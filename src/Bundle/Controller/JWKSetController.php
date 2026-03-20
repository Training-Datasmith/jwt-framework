<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Controller;

use Symfony\Component\Http_Foundation\Response;
final readonly class Jwk_Set_Controller
{
    public function __construct(private string $jwkset)
    {
    }
    public function __invoke(): Response
    {
        return new Response($this->jwkset, Response::HTTP_OK, ['Content-Type' => 'application/jwk-set+json; charset=UTF-8']);
    }
}