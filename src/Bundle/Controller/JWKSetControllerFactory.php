<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Controller;

use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
final readonly class Jwk_Set_Controller_Factory
{
    public function create(Jwk_Set $jwkset): Jwk_Set_Controller
    {
        return new Jwk_Set_Controller(Json_Converter::encode($jwkset));
    }
}