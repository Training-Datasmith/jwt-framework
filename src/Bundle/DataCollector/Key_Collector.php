<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Key_Management\Analyzer\Key_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Keyset_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Message_Bag;
use Override;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Var_Dumper\Cloner\Var_Cloner;
use Throwable;
final class Key_Collector implements Collector
{
    /**
     * @var array<JWK>
     */
    private array $jwks = [];
    /**
     * @var array<JWKSet>
     */
    private array $jwksets = [];
    public function __construct(private readonly ?Key_Analyzer_Manager $jwk_analyzer_manager = null, private readonly ?Keyset_Analyzer_Manager $jwkset_analyzer_manager = null)
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->collect_jwk($data);
        $this->collect_jwk_set($data);
    }
    public function add_jwk(string $id, JWK $jwk): void
    {
        $this->jwks[$id] = $jwk;
    }
    public function add_jwk_set(string $id, Jwk_Set $jwkset): void
    {
        $this->jwksets[$id] = $jwkset;
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_jwk(array &$data): void
    {
        $cloner = new Var_Cloner();
        $data['key']['jwk'] = [];
        foreach ($this->jwks as $id => $jwk) {
            $data['key']['jwk'][$id] = ['jwk' => $cloner->clone_var($jwk), 'analyze' => $this->jwk_analyzer_manager === null ? [] : $this->jwk_analyzer_manager->analyze($jwk)];
        }
    }
    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function collect_jwk_set(array &$data): void
    {
        $cloner = new Var_Cloner();
        $data['key']['jwkset'] = [];
        foreach ($this->jwksets as $id => $jwkset) {
            $analyze = [];
            $analyze_jwk_set = new Message_Bag();
            if ($this->jwk_analyzer_manager !== null) {
                foreach ($jwkset as $kid => $jwk) {
                    $analyze[$kid] = $this->jwk_analyzer_manager->analyze($jwk);
                }
            }
            if ($this->jwkset_analyzer_manager !== null) {
                $analyze_jwk_set = $this->jwkset_analyzer_manager->analyze($jwkset);
            }
            $data['key']['jwkset'][$id] = ['jwkset' => $cloner->clone_var($jwkset), 'analyze' => $analyze, 'analyze_jwkset' => $analyze_jwk_set];
        }
    }
}