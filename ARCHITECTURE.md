# Architecture: jwt-framework

## Purpose
web-token/jwt-framework — a comprehensive PHP implementation of the JOSE (JSON Object Signing and Encryption) family of standards: JWS (RFC 7515), JWE (RFC 7516), JWK (RFC 7517), JWT (RFC 7519). Includes a Symfony bundle for DI integration.

## Directory Structure
```
src/
  Bundle/JoseFramework/           # Symfony bundle — DI configuration, compiler passes
  Component/
    Checker/                      # Claim and header checkers (exp, nbf, iss, aud, etc.)
    Console/                      # Symfony Console commands for key generation/analysis
    Core/                         # Algorithm manager, JWK, JWKS, JSON converter
    Encryption/                   # JWE builder/decrypter + algorithms (AES-GCM, RSA, ECDH-ES, PBES2)
    KeyManagement/                # Key creation, conversion, key analyzers
    NestedToken/                  # Nested JWT (signed then encrypted)
    Signature/                    # JWS builder/verifier + algorithms (RS/ES/PS/HS, EdDSA, none)
performance/                      # PHPBench benchmarks for JWE and JWS operations
tests/
  Bundle/JoseFramework/           # Symfony bundle integration tests
  Component/                      # Per-component unit/functional tests
```

## Key Design Decisions
- **Component architecture** — each JOSE concern (signing, encryption, key management, claim checking) is an independent `Component` that can be used standalone without the Symfony bundle.
- **Algorithm manager** — algorithms are registered in an `Algorithm_Manager` and looked up by JWA identifier (e.g., `RS256`, `A128GCM`). This makes algorithm selection data-driven and allows custom algorithms to be registered.
- **Builder/Loader pattern** — token creation uses a `JWS_Builder` / `JWE_Builder`; token consumption uses a `JWS_Loader` / `JWE_Loader`. Builders are immutable (each method returns a new instance).
- **Key analyzers** — the `KeyManagement\Analyzer` subsystem inspects JWK objects and emits warnings about weak or misconfigured keys.
- **Symfony DI integration** — the bundle wires all components into the Symfony container using tagged services and compiler passes.

## Extension Points
- Implement `Algorithm` interface to add a custom signing or encryption algorithm.
- Implement `Claim_Checker` or `Header_Checker` to add custom JWT validation logic.
- Register custom key analyzers by implementing `Key_Analyzer` and tagging as `jose.key_analyzer`.

## Dependency Flow
```
JWS_Builder
  ├─ payload + headers
  ├─ Algorithm_Manager (selects algorithm by 'alg' header)
  └─ JWK (signing key)
       → compact / JSON-serialized JWS

JWS_Loader
  ├─ JWS_Serializer_Manager (deserialize)
  ├─ JWS_Verifier (verify signature using Algorithm_Manager + JWK/JWKS)
  └─ Header_Checker_Manager + Claim_Checker_Manager → validated claims
```
