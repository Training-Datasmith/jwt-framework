<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function array_key_exists;
use function count;
use function sprintf;
/**
 * This class manages claim checkers and performs claim checks.
 * @see \Jose\Tests\Component\Checker\ClaimCheckerManagerTest
 */
class Claim_Checker_Manager
{
    /**
     * @var ClaimChecker[]
     */
    private array $checkers = [];
    /**
     * @param ClaimChecker[] $checkers
     */
    public function __construct(iterable $checkers)
    {
        foreach ($checkers as $checker) {
            $this->add($checker);
        }
    }
    /**
     * This method returns all checkers handled by this manager.
     *
     * @return ClaimChecker[]
     */
    public function get_checkers(): array
    {
        return $this->checkers;
    }
    /**
     * This method checks all the claims passed as argument. All claims are checked against the claim checkers. If one
     * fails, the InvalidClaimException is thrown.
     *
     * This method returns an array with all checked claims. It is up to the implementor to decide use the claims that
     * have not been checked.
     *
     * @param string[] $mandatoryClaims
     */
    public function check(array $claims, array $mandatory_claims = []): array
    {
        $this->check_mandatory_claims($mandatory_claims, $claims);
        $checked_claims = [];
        foreach ($this->checkers as $claim => $checker) {
            if (array_key_exists($claim, $claims)) {
                $checker->check_claim($claims[$claim]);
                $checked_claims[$claim] = $claims[$claim];
            }
        }
        return $checked_claims;
    }
    private function add(Claim_Checker $checker): void
    {
        $claim = $checker->supported_claim();
        $this->checkers[$claim] = $checker;
    }
    /**
     * @param string[] $mandatoryClaims
     */
    private function check_mandatory_claims(array $mandatory_claims, array $claims): void
    {
        if (count($mandatory_claims) === 0) {
            return;
        }
        $diff = array_keys(array_diff_key(array_flip($mandatory_claims), $claims));
        if (count($diff) !== 0) {
            throw new Missing_Mandatory_Claim_Exception(sprintf('The following claims are mandatory: %s.', implode(', ', $diff)), $diff);
        }
    }
}