<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\Jwk_Set;
final class Keyset_Analyzer_Manager
{
    /**
     * @var KeysetAnalyzer[]
     */
    private array $analyzers = [];
    /**
     * Adds a Keyset Analyzer to the manager.
     */
    public function add(Keyset_Analyzer $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }
    /**
     * This method will analyze the JWKSet object using all analyzers. It returns a message bag that may contains
     * messages.
     */
    public function analyze(Jwk_Set $jwkset): Message_Bag
    {
        $bag = new Message_Bag();
        foreach ($this->analyzers as $analyzer) {
            $analyzer->analyze($jwkset, $bag);
        }
        return $bag;
    }
}