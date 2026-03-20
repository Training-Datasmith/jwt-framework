<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use InvalidArgumentException;
use Jose\Component\Checker\Header_Checker;
use Jose\Component\Checker\Token_Type_Support;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use function sprintf;
final class Header_Checker_Manager_Factory
{
    /**
     * @var HeaderChecker[]
     */
    private array $checkers = [];
    /**
     * @var TokenTypeSupport[]
     */
    private array $token_types = [];
    public function __construct(private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * This method creates a Header Checker Manager and populate it with the header parameter checkers found based on
     * the alias. If the alias is not supported, an InvalidArgumentException is thrown.
     *
     * @param string[] $aliases
     */
    public function create(array $aliases): Header_Checker_Manager
    {
        $checkers = [];
        foreach ($aliases as $alias) {
            if (!isset($this->checkers[$alias])) {
                throw new InvalidArgumentException(sprintf('The header checker with the alias "%s" is not supported.', $alias));
            }
            $checkers[] = $this->checkers[$alias];
        }
        return new Header_Checker_Manager($checkers, $this->token_types, $this->event_dispatcher);
    }
    /**
     * This method adds a header parameter checker to this factory. The checker is uniquely identified by an alias. This
     * allows the same header parameter checker to be added twice (or more) using several configuration options.
     */
    public function add(string $alias, Header_Checker $checker): void
    {
        $this->checkers[$alias] = $checker;
    }
    /**
     * This method adds a token type support to this factory.
     */
    public function add_token_type_support(Token_Type_Support $token_type): void
    {
        $this->token_types[] = $token_type;
    }
    /**
     * Returns all header parameter checker aliases supported by this factory.
     *
     * @return string[]
     */
    public function aliases(): array
    {
        return array_keys($this->checkers);
    }
    /**
     * Returns all header parameter checkers supported by this factory.
     *
     * @return HeaderChecker[]
     */
    public function all(): array
    {
        return $this->checkers;
    }
}