<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Routing;

use function assert;
use Override;
use function sprintf;
use Symfony\Component\Config\Loader\Loader_Interface;
use Symfony\Component\Config\Loader\Loader_Resolver_Interface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route_Collection;
final class Jwk_Set_Loader implements Loader_Interface
{
    private readonly Route_Collection $routes;
    private null|Loader_Resolver_Interface $resolver = null;
    public function __construct()
    {
        $this->routes = new Route_Collection();
    }
    public function add(string $pattern, string $name): void
    {
        $defaults = ['_controller' => $name];
        $route = new Route($pattern, $defaults);
        $this->routes->add(sprintf('jwkset_%s', $name), $route);
    }
    #[Override]
    public function load(mixed $resource, ?string $type = null): Route_Collection
    {
        return $this->routes;
    }
    #[Override]
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'jwkset';
    }
    #[Override]
    public function get_resolver(): Loader_Resolver_Interface
    {
        assert($this->resolver !== null, 'Resolver is not set.');
        return $this->resolver;
    }
    #[Override]
    public function set_resolver(Loader_Resolver_Interface $resolver): void
    {
        $this->resolver = $resolver;
    }
}