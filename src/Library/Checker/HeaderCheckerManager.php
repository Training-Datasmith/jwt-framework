<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\JWT;
use function sprintf;
/**
 * This class is a factory to create Header Checker Managers.
 *
 * It allows to add header parameter checkers and token type supports.
 * The factory is responsible to create a Header Checker Manager with the header parameter checkers found based
 */
class Header_Checker_Manager
{
    /**
     * @var HeaderChecker[]
     */
    private array $checkers = [];
    /**
     * @var TokenTypeSupport[]
     */
    private array $token_types = [];
    /**
     * @param HeaderChecker[] $checkers
     * @param TokenTypeSupport[] $tokenTypes
     */
    public function __construct(iterable $checkers, iterable $token_types)
    {
        foreach ($checkers as $checker) {
            $this->add($checker);
        }
        foreach ($token_types as $token_type) {
            $this->add_token_type_support($token_type);
        }
    }
    /**
     * This method returns all checkers handled by this manager.
     *
     * @return HeaderChecker[]
     */
    public function get_checkers(): array
    {
        return $this->checkers;
    }
    /**
     * This method checks all the header parameters passed as argument. All header parameters are checked against the
     * header parameter checkers. If one fails, the InvalidHeaderException is thrown.
     *
     * @param string[] $mandatoryHeaderParameters
     */
    public function check(JWT $jwt, int $index, array $mandatory_header_parameters = []): void
    {
        foreach ($this->token_types as $token_type) {
            if ($token_type->supports($jwt)) {
                $protected = [];
                $unprotected = [];
                $token_type->retrieve_token_headers($jwt, $index, $protected, $unprotected);
                $this->check_duplicated_header_parameters($protected, $unprotected);
                $this->check_mandatory_header_parameters($mandatory_header_parameters, $protected, $unprotected);
                $this->check_headers($protected, $unprotected);
                return;
            }
        }
        throw new InvalidArgumentException('Unsupported token type.');
    }
    private function add_token_type_support(Token_Type_Support $token_type): void
    {
        $this->token_types[] = $token_type;
    }
    private function add(Header_Checker $checker): void
    {
        $header = $checker->supported_header();
        $this->checkers[$header] = $checker;
    }
    private function check_duplicated_header_parameters(array $header1, array $header2): void
    {
        $inter = array_intersect_key($header1, $header2);
        if (count($inter) !== 0) {
            throw new InvalidArgumentException(sprintf('The header contains duplicated entries: %s.', implode(', ', array_keys($inter))));
        }
    }
    /**
     * @param string[] $mandatoryHeaderParameters
     */
    private function check_mandatory_header_parameters(array $mandatory_header_parameters, array $protected, array $unprotected): void
    {
        if (count($mandatory_header_parameters) === 0) {
            return;
        }
        $diff = array_keys(array_diff_key(array_flip($mandatory_header_parameters), array_merge($protected, $unprotected)));
        if (count($diff) !== 0) {
            throw new Missing_Mandatory_Header_Parameter_Exception(sprintf('The following header parameters are mandatory: %s.', implode(', ', $diff)), $diff);
        }
    }
    private function check_headers(array $protected, array $header): void
    {
        $checked_header_parameters = [];
        foreach ($this->checkers as $header_parameter => $checker) {
            if ($checker->protected_header_only()) {
                if (array_key_exists($header_parameter, $protected)) {
                    $checker->check_header($protected[$header_parameter]);
                    $checked_header_parameters[] = $header_parameter;
                } elseif (array_key_exists($header_parameter, $header)) {
                    throw new Invalid_Header_Exception(sprintf('The header parameter "%s" must be protected.', $header_parameter), $header_parameter, $header[$header_parameter]);
                }
            } else if (array_key_exists($header_parameter, $protected)) {
                $checker->check_header($protected[$header_parameter]);
                $checked_header_parameters[] = $header_parameter;
            } elseif (array_key_exists($header_parameter, $header)) {
                $checker->check_header($header[$header_parameter]);
                $checked_header_parameters[] = $header_parameter;
            }
        }
        $this->check_critical_header($protected, $header, $checked_header_parameters);
    }
    private function check_critical_header(array $protected, array $header, array $checked_header_parameters): void
    {
        if (array_key_exists('crit', $protected)) {
            if (!is_array($protected['crit'])) {
                throw new Invalid_Header_Exception('The header "crit" must be a list of header parameters.', 'crit', $protected['crit']);
            }
            $diff = array_diff($protected['crit'], $checked_header_parameters);
            if (count($diff) !== 0) {
                throw new Invalid_Header_Exception(sprintf('One or more header parameters are marked as critical, but they are missing or have not been checked: %s.', implode(', ', array_values($diff))), 'crit', $protected['crit']);
            }
        } elseif (array_key_exists('crit', $header)) {
            throw new Invalid_Header_Exception('The header parameter "crit" must be protected.', 'crit', $header['crit']);
        }
    }
}