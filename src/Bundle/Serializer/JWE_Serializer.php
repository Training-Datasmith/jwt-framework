<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Serializer;

use function in_array;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use LogicException;
use Override;
use Symfony\Component\Serializer\Normalizer\Denormalizer_Interface;
final readonly class Jwe_Serializer implements Denormalizer_Interface
{
    private Jwe_Serializer_Manager $serializer_manager;
    public function __construct(Jwe_Serializer_Manager_Factory $serializer_manager_factory, ?Jwe_Serializer_Manager $serializer_manager = null)
    {
        if ($serializer_manager === null) {
            $serializer_manager = $serializer_manager_factory->create($serializer_manager_factory->names());
        }
        $this->serializer_manager = $serializer_manager;
    }
    #[Override]
    public function get_supported_types(?string $format): array
    {
        return [JWE::class => class_exists(Jwe_Serializer_Manager::class) && $this->format_supported($format)];
    }
    #[Override]
    public function supports_denormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === JWE::class && class_exists(Jwe_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): JWE
    {
        if ($data instanceof JWE === false) {
            throw new LogicException('Expected data to be a JWE.');
        }
        return $data;
    }
    /**
     * Check if format is supported.
     */
    private function format_supported(?string $format): bool
    {
        return $format !== null && in_array(strtolower($format), $this->serializer_manager->names(), true);
    }
}