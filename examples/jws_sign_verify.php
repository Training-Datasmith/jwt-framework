<?php

declare(strict_types=1);

/**
 * Example: sign and verify a JWS token with the jwt-framework Symfony bundle (web-token).
 *
 * This example uses the low-level component API directly (no Symfony DI container needed).
 *
 * Run from the jwt-framework project root:
 *   php examples/jws_sign_verify.php
 *
 * Requires: composer require web-token/jwt-signature web-token/jwt-signature-algorithm-hmac
 */

require __DIR__ . '/../vendor/autoload.php';

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Signature\JWSTokenSupport;

// --- Create a symmetric JWK (HMAC-SHA256) ---
$jwk = new JWK([
    'kty' => 'oct',
    'use' => 'sig',
    'k'   => base64_encode(random_bytes(32)),
]);

$algorithmManager = new AlgorithmManager([new HS256()]);
$serializer       = new CompactSerializer();

// --- Build and sign a JWS ---
$builder = new JWSBuilder($algorithmManager);

$payload = json_encode([
    'iss' => 'https://example.com',
    'sub' => 'user-1',
    'aud' => 'https://api.example.com',
    'iat' => time(),
    'exp' => time() + 3600,
    'name'=> 'Alice',
]);

$jws = $builder
    ->create()
    ->withPayload($payload)
    ->addSignature($jwk, ['alg' => 'HS256', 'typ' => 'JWT'])
    ->build();

$token = $serializer->serialize($jws, 0);
echo "Token (first 80 chars): " . substr($token, 0, 80) . "...\n\n";

// --- Verify the JWS ---
$serializerManager = new JWSSerializerManager([$serializer]);
$verifier          = new JWSVerifier($algorithmManager);

$loadedJws = $serializerManager->unserialize($token);
$isValid   = $verifier->verifyWithKey($loadedJws, $jwk, 0);
echo "Signature valid: " . ($isValid ? 'yes' : 'no') . "\n";

// --- Check headers ---
$headerCheckerManager = new HeaderCheckerManager(
    [new AlgorithmChecker(['HS256'])],
    [new JWSTokenSupport()]
);
$headerCheckerManager->check($loadedJws, 0);
echo "Header check passed.\n";

// --- Check claims ---
$claimCheckerManager = new ClaimCheckerManager([new ExpirationTimeChecker()]);
$claims = json_decode($loadedJws->getPayload(), true);
$claimCheckerManager->check($claims);
echo "Claim check passed. Subject: " . $claims['sub'] . "\n";
