<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Helper;

use function is_array;
use Symfony\Component\Dependency_Injection\Container_Builder;
final readonly class Configuration_Helper
{
    final public const BUNDLE_ALIAS = 'jose';
    /**
     * @param string[] $signatureAlgorithms
     */
    public static function add_jws_builder(Container_Builder $container, string $name, array $signature_algorithms, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jws' => ['builders' => [$name => ['is_public' => $is_public, 'signature_algorithms' => $signature_algorithms, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jws');
    }
    /**
     * @param string[] $signatureAlgorithms
     */
    public static function add_jws_verifier(Container_Builder $container, string $name, array $signature_algorithms, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jws' => ['verifiers' => [$name => ['is_public' => $is_public, 'signature_algorithms' => $signature_algorithms, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jws');
    }
    /**
     * @param string[] $serializers
     */
    public static function add_jws_serializer(Container_Builder $container, string $name, array $serializers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jws' => ['serializers' => [$name => ['is_public' => $is_public, 'serializers' => $serializers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jws');
    }
    /**
     * @param string[] $serializers
     * @param string[] $signatureAlgorithms
     * @param string[] $header_checkers
     */
    public static function add_jws_loader(Container_Builder $container, string $name, array $serializers, array $signature_algorithms, array $header_checkers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jws' => ['loaders' => [$name => ['is_public' => $is_public, 'serializers' => $serializers, 'signature_algorithms' => $signature_algorithms, 'header_checkers' => $header_checkers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jws');
    }
    /**
     * @param string[] $jweSerializers
     * @param string[] $encryptionAlgorithms
     * @param string[] $jweHeaderCheckers
     * @param string[] $jwsSerializers
     * @param string[] $signatureAlgorithms
     * @param string[] $jwsHeaderCheckers
     */
    public static function add_nested_token_loader(Container_Builder $container, string $name, array $jwe_serializers, array $encryption_algorithms, array $jwe_header_checkers, array $jws_serializers, array $signature_algorithms, array $jws_header_checkers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['nested_token' => ['loaders' => [$name => ['is_public' => $is_public, 'jwe_serializers' => $jwe_serializers, 'encryption_algorithms' => $encryption_algorithms, 'jwe_header_checkers' => $jwe_header_checkers, 'jws_serializers' => $jws_serializers, 'signature_algorithms' => $signature_algorithms, 'jws_header_checkers' => $jws_header_checkers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'nested_token');
    }
    /**
     * @param string[] $jweSerializers
     * @param string[] $encryptionAlgorithms
     * @param string[] $jwsSerializers
     * @param string[] $signatureAlgorithms
     */
    public static function add_nested_token_builder(Container_Builder $container, string $name, array $jwe_serializers, array $encryption_algorithms, array $jws_serializers, array $signature_algorithms, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['nested_token' => ['builders' => [$name => ['is_public' => $is_public, 'jwe_serializers' => $jwe_serializers, 'encryption_algorithms' => $encryption_algorithms, 'jws_serializers' => $jws_serializers, 'signature_algorithms' => $signature_algorithms, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'nested_token');
    }
    /**
     * @param string[] $serializers
     */
    public static function add_jwe_serializer(Container_Builder $container, string $name, array $serializers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jwe' => ['serializers' => [$name => ['is_public' => $is_public, 'serializers' => $serializers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jwe');
    }
    /**
     * @param string[] $serializers
     * @param string[] $encryptionAlgorithms
     * @param string[] $header_checkers
     */
    public static function add_jwe_loader(Container_Builder $container, string $name, array $serializers, array $encryption_algorithms, array $header_checkers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jwe' => ['loaders' => [$name => ['is_public' => $is_public, 'serializers' => $serializers, 'encryption_algorithms' => $encryption_algorithms, 'header_checkers' => $header_checkers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jwe');
    }
    /**
     * @param string[] $claimCheckers
     */
    public static function add_claim_checker(Container_Builder $container, string $name, array $claim_checkers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['checkers' => ['claims' => [$name => ['is_public' => $is_public, 'claims' => $claim_checkers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'checkers');
    }
    /**
     * @param string[] $headerCheckers
     */
    public static function add_header_checker(Container_Builder $container, string $name, array $header_checkers, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['checkers' => ['headers' => [$name => ['is_public' => $is_public, 'headers' => $header_checkers, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'checkers');
    }
    public static function add_key(Container_Builder $container, string $name, string $type, array $parameters, bool $is_public = true, array $tags = []): void
    {
        $parameters['is_public'] = $is_public;
        $parameters['tags'] = $tags;
        $config = [self::BUNDLE_ALIAS => ['keys' => [$name => [$type => $parameters]]]];
        self::update_jose_configuration($container, $config, 'keys');
    }
    public static function add_keyset(Container_Builder $container, string $name, string $type, array $parameters, bool $is_public = true, array $tags = []): void
    {
        $parameters['is_public'] = $is_public;
        $parameters['tags'] = $tags;
        $config = [self::BUNDLE_ALIAS => ['key_sets' => [$name => [$type => $parameters]]]];
        self::update_jose_configuration($container, $config, 'key_sets');
    }
    public static function add_key_uri(Container_Builder $container, string $name, array $parameters, bool $is_public = true, array $tags = []): void
    {
        $parameters['is_public'] = $is_public;
        $parameters['tags'] = $tags;
        $config = [self::BUNDLE_ALIAS => ['jwk_uris' => [$name => $parameters]]];
        self::update_jose_configuration($container, $config, 'jwk_uris');
    }
    public static function add_jwe_builder(Container_Builder $container, string $name, array $encryption_algorithm, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jwe' => ['builders' => [$name => ['is_public' => $is_public, 'encryption_algorithms' => $encryption_algorithm, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jwe');
    }
    public static function add_jwe_decrypter(Container_Builder $container, string $name, array $encryption_algorithm, bool $is_public = true, array $tags = []): void
    {
        $config = [self::BUNDLE_ALIAS => ['jwe' => ['decrypters' => [$name => ['is_public' => $is_public, 'encryption_algorithms' => $encryption_algorithm, 'tags' => $tags]]]]];
        self::update_jose_configuration($container, $config, 'jwe');
    }
    private static function update_jose_configuration(Container_Builder $container, array $config, string $element): void
    {
        $jose_config = current($container->get_extension_config(self::BUNDLE_ALIAS));
        if (!is_array($jose_config)) {
            $jose_config = [];
        }
        if (!isset($jose_config[$element])) {
            $jose_config[$element] = [];
        }
        $jose_config[$element] = array_merge($jose_config[$element], $config[self::BUNDLE_ALIAS][$element]);
        $container->prepend_extension_config(self::BUNDLE_ALIAS, $jose_config);
    }
}