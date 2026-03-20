<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use InvalidArgumentException;
use Jose\Component\Checker\Claim_Checker;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use function sprintf;
final class Claim_Checker_Manager_Factory
{
    /**
     * @var ClaimChecker[]
     */
    private array $checkers = [];
    public function __construct(private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * This method creates a Claim Checker Manager and populate it with the claim checkers found based on the alias. If
     * the alias is not supported, an InvalidArgumentException is thrown.
     *
     * @param string[] $aliases
     */
    public function create(array $aliases): Claim_Checker_Manager
    {
        $checkers = [];
        foreach ($aliases as $alias) {
            if (!isset($this->checkers[$alias])) {
                throw new InvalidArgumentException(sprintf('The claim checker with the alias "%s" is not supported.', $alias));
            }
            $checkers[] = $this->checkers[$alias];
        }
        return new Claim_Checker_Manager($checkers, $this->event_dispatcher);
    }
    /**
     * This method adds a claim checker to this factory.
     */
    public function add(string $alias, Claim_Checker $checker): void
    {
        $this->checkers[$alias] = $checker;
    }
    /**
     * Returns all claim checker aliases supported by this factory.
     *
     * @return string[]
     */
    public function aliases(): array
    {
        return array_keys($this->checkers);
    }
    /**
     * Returns all claim checkers supported by this factory.
     *
     * @return ClaimChecker[]
     */
    public function all(): array
    {
        return $this->checkers;
    }
}