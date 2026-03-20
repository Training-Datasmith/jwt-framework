<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Env_Var_Processor;

use Closure;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Override;
use RuntimeException;
use function sprintf;
use Symfony\Component\Dependency_Injection\Env_Var_Processor_Interface;
final readonly class Key_Env_Var_Processor implements Env_Var_Processor_Interface
{
    #[Override]
    public function get_env(string $prefix, string $name, Closure $get_env): mixed
    {
        $env = $get_env($name);
        return match ($prefix) {
            'jwk' => JWK::create_from_json($env),
            'jwkset' => Jwk_Set::create_from_json($env),
            default => throw new RuntimeException(sprintf('Unsupported prefix "%s".', $prefix)),
        };
    }
    #[Override]
    public static function get_provided_types(): array
    {
        return ['jwk' => 'string', 'jwkset' => 'string'];
    }
}