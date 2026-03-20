<?php

declare (strict_types=1);
namespace Jose\Component\Signature\Serializer;

use Jose\Component\Signature\JWS;
interface Jws_Serializer
{
    /**
     * The name of the serialization.
     */
    public function name(): string;
    public function display_name(): string;
    /**
     * Converts a JWS into a string.
     */
    public function serialize(JWS $jws, ?int $signature_index = null): string;
    /**
     * Loads data and return a JWS object.
     *
     * @param string $input A string that represents a JWS
     */
    public function unserialize(string $input): JWS;
}