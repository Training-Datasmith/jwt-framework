<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Data_Collector;

use function array_key_exists;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\Algorithm_Manager_Factory;
use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
use Jose\Component\Signature\Algorithm\Mac_Algorithm;
use Jose\Component\Signature\Algorithm\Signature_Algorithm;
use Override;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Throwable;
final readonly class Algorithm_Collector implements Collector
{
    public function __construct(private Algorithm_Manager_Factory $algorithm_manager_factory)
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function collect(array &$data, Request $request, Response $response, ?Throwable $exception = null): void
    {
        $algorithms = $this->algorithm_manager_factory->all();
        $data['algorithm'] = ['messages' => $this->get_algorithm_messages(), 'algorithms' => []];
        $signature_algorithms = 0;
        $mac_algorithms = 0;
        $key_encryption_algorithms = 0;
        $content_encryption_algorithms = 0;
        foreach ($algorithms as $alias => $algorithm) {
            $type = $this->get_algorithm_type($algorithm, $signature_algorithms, $mac_algorithms, $key_encryption_algorithms, $content_encryption_algorithms);
            if (!array_key_exists($type, $data['algorithm']['algorithms'])) {
                $data['algorithm']['algorithms'][$type] = [];
            }
            $data['algorithm']['algorithms'][$type][$alias] = ['name' => $algorithm->name()];
        }
        $data['algorithm']['types'] = ['signature' => $signature_algorithms, 'mac' => $mac_algorithms, 'key_encryption' => $key_encryption_algorithms, 'content_encryption' => $content_encryption_algorithms];
    }
    private function get_algorithm_type(Algorithm $algorithm, int &$signature_algorithms, int &$mac_algorithms, int &$key_encryption_algorithms, int &$content_encryption_algorithms): string
    {
        switch (true) {
            case $algorithm instanceof Signature_Algorithm:
                $signature_algorithms++;
                return 'Signature';
            case $algorithm instanceof Mac_Algorithm:
                $mac_algorithms++;
                return 'MAC';
            case $algorithm instanceof Key_Encryption_Algorithm:
                $key_encryption_algorithms++;
                return 'Key Encryption';
            case $algorithm instanceof Content_Encryption_Algorithm:
                $content_encryption_algorithms++;
                return 'Content Encryption';
            default:
                return 'Unknown';
        }
    }
    /**
     * @return array<string, array<string, string>>
     */
    private function get_algorithm_messages(): array
    {
        return ['none' => ['severity' => 'severity-low', 'message' => 'This algorithm is not secured. Please use with caution.'], 'HS256/64' => ['severity' => 'severity-low', 'message' => 'Experimental. Please use for testing purpose only.'], 'RS1' => ['severity' => 'severity-high', 'message' => 'Experimental. Please use for testing purpose only. SHA-1 hashing function is not recommended.'], 'RS256' => ['severity' => 'severity-medium', 'message' => 'RSAES-PKCS1-v1_5 based algorithms are not recommended.'], 'RS384' => ['severity' => 'severity-medium', 'message' => 'RSAES-PKCS1-v1_5 based algorithms are not recommended.'], 'RS512' => ['severity' => 'severity-medium', 'message' => 'RSAES-PKCS1-v1_5 based algorithms are not recommended.'], 'HS1' => ['severity' => 'severity-high', 'message' => 'This algorithm has known vulnerabilities. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-17">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-17</a>. SHA-1 hashing function is not recommended.'], 'A128CTR' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'A192CTR' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'A256CTR' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'A128CBC' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'A192CBC' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'A256CBC' => ['severity' => 'severity-high', 'message' => 'This algorithm is prohibited. For compatibility with old application only. See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-11</a>.'], 'chacha20-poly1305' => ['severity' => 'severity-low', 'message' => 'Experimental. Please use for testing purpose only.'], 'RSA-OAEP-384' => ['severity' => 'severity-low', 'message' => 'Experimental. Please use for testing purpose only.'], 'RSA-OAEP-512' => ['severity' => 'severity-low', 'message' => 'Experimental. Please use for testing purpose only.'], 'A128CCM-16-64' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A256CCM-16-64' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A128CCM-64-64' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A256CCM-64-64' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A128CCM-16-128' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A256CCM-16-128' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A128CCM-64-128' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'A256CCM-64-128' => ['severity' => 'severity-low', 'message' => 'Experimental and subject to changes. Please use for testing purpose only.'], 'RSA1_5' => ['severity' => 'severity-high', 'message' => 'This algorithm is not secured (known attacks). See <a target="_blank" href="https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-5">https://tools.ietf.org/html/draft-irtf-cfrg-webcrypto-algorithms-00#section-5</a>.']];
    }
}