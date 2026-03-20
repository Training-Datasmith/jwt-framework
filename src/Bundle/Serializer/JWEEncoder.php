<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Serializer;

use Exception;
use function in_array;
use function is_int;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use LogicException;
use Override;
use function sprintf;
use Symfony\Component\Serializer\Encoder\Decoder_Interface;
use Symfony\Component\Serializer\Encoder\Encoder_Interface;
use Symfony\Component\Serializer\Encoder\Normalization_Aware_Interface;
use Symfony\Component\Serializer\Exception\Not_Encodable_Value_Exception;
use Throwable;
final readonly class Jwe_Encoder implements Encoder_Interface, Decoder_Interface, Normalization_Aware_Interface
{
    private readonly Jwe_Serializer_Manager $serializer_manager;
    public function __construct(Jwe_Serializer_Manager_Factory $serializer_manager_factory, ?Jwe_Serializer_Manager $serializer_manager = null)
    {
        if ($serializer_manager === null) {
            $serializer_manager = $serializer_manager_factory->create($serializer_manager_factory->names());
        }
        $this->serializer_manager = $serializer_manager;
    }
    #[Override]
    public function supports_encoding(string $format, array $context = []): bool
    {
        return class_exists(Jwe_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function supports_decoding(string $format, array $context = []): bool
    {
        return class_exists(Jwe_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function encode(mixed $data, string $format, array $context = []): string
    {
        if ($data instanceof JWE === false) {
            throw new LogicException('Expected data to be a JWE.');
        }
        try {
            return $this->serializer_manager->serialize(strtolower($format), $data, $this->get_recipient_index($context));
        } catch (Throwable $ex) {
            throw new Not_Encodable_Value_Exception(sprintf('Cannot encode JWE to %s format.', $format), 0, $ex);
        }
    }
    #[Override]
    public function decode(string $data, string $format, array $context = []): JWE
    {
        try {
            return $this->serializer_manager->unserialize($data);
        } catch (Exception $ex) {
            throw new Not_Encodable_Value_Exception(sprintf('Cannot decode JWE from %s format.', $format), 0, $ex);
        }
    }
    /**
     * Get JWE recipient index from context.
     */
    private function get_recipient_index(array $context): int
    {
        if (isset($context['recipient_index']) && is_int($context['recipient_index'])) {
            return $context['recipient_index'];
        }
        return 0;
    }
    /**
     * Check if format is supported.
     */
    private function format_supported(?string $format): bool
    {
        return $format !== null && in_array(strtolower($format), $this->serializer_manager->names(), true);
    }
}