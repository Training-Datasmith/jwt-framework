<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\JWK;
final class Key_Analyzer_Manager
{
    /**
     * @var KeyAnalyzer[]
     */
    private array $analyzers = [];
    /**
     * Adds a Key Analyzer to the manager.
     */
    public function add(Key_Analyzer $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }
    /**
     * This method will analyze the JWK object using all analyzers. It returns a message bag that may contains messages.
     */
    public function analyze(JWK $jwk): Message_Bag
    {
        $bag = new Message_Bag();
        foreach ($this->analyzers as $analyzer) {
            $analyzer->analyze($jwk, $bag);
        }
        return $bag;
    }
}