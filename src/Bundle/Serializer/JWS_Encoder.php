<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Serializer;

use Exception;
use function in_array;
use function is_int;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
use LogicException;
use Override;
use function sprintf;
use Symfony\Component\Serializer\Encoder\Decoder_Interface;
use Symfony\Component\Serializer\Encoder\Encoder_Interface;
use Symfony\Component\Serializer\Encoder\Normalization_Aware_Interface;
use Symfony\Component\Serializer\Exception\Not_Encodable_Value_Exception;
final readonly class Jws_Encoder implements Encoder_Interface, Decoder_Interface, Normalization_Aware_Interface
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
    public function supports_encoding(string $format, array $context = []): bool
    {
        return class_exists(Jws_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function supports_decoding(string $format, array $context = []): bool
    {
        return class_exists(Jws_Serializer_Manager::class) && $this->format_supported($format);
    }
    #[Override]
    public function encode($data, string $format, array $context = []): string
    {
        if ($data instanceof JWS === false) {
            throw new LogicException('Expected data to be a JWS.');
        }
        try {
            return $this->serializer_manager->serialize(strtolower($format), $data, $this->get_signature_index($context));
        } catch (Exception $ex) {
            throw new Not_Encodable_Value_Exception(sprintf('Cannot encode JWS to %s format.', $format), 0, $ex);
        }
    }
    #[Override]
    public function decode(string $data, string $format, array $context = []): JWS
    {
        try {
            return $this->serializer_manager->unserialize($data);
        } catch (Exception $ex) {
            throw new Not_Encodable_Value_Exception(sprintf('Cannot decode JWS from %s format.', $format), 0, $ex);
        }
    }
    /**
     * Get JWS signature index from context.
     */
    private function get_signature_index(array $context): int
    {
        if (isset($context['signature_index']) && is_int($context['signature_index'])) {
            return $context['signature_index'];
        }
        return 0;
    }
    /**
     * Check if format is supported.
     */
    private function format_supported(?string $format): bool
    {
        return $format !== null && in_array(strtolower($format), $this->serializer_manager->list(), true);
    }
}