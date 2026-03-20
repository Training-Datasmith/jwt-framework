<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Serializer;

use function in_array;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
use LogicException;
use Override;
use Symfony\Component\Serializer\Normalizer\Denormalizer_Interface;
final readonly class Jws_Serializer implements Denormalizer_Interface
{
    private readonly Jws_Serializer_Manager $serializer_manager;
    public function __construct(Jws_Serializer_Manager_Factory $serializer_manager_factory, ?Jws_Serializer_Manager $serializer_manager = null)
    {
        if ($serializer_manager === null) {
            $serializer_manager = $serializer_manager_factory->create($serializer_manager_factory->names());
        }
        $this->serializer_manager = $serializer_manager;
    }
    #[Override]
    public function get_supported_types(?string $format): array
    {
        return [JWS::class => class_exists(Jws_Serializer_Manager::class) && $this->format_supported($format)];
    }
    #[Override]
    public function supports_denormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === JWS::class && class_exists(Jws_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): JWS
    {
        if ($data instanceof JWS === false) {
            throw new LogicException('Expected data to be a JWS.');
        }
        return $data;
    }
    /**
     * Check if format is supported.
     */
    private function format_supported(?string $format): bool
    {
        return $format !== null && in_array(strtolower($format), $this->serializer_manager->list(), true);
    }
}