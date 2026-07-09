<?php

namespace GlobalPayments\Api\Tests\Utils;

use GlobalPayments\Api\Terminals\Abstractions\IRequestIdProvider;

/**
 * Record/replay harness for CI tests.
 */
class CiTestingHarness
{
    const LOCKED = 'Locked';
    const UNLOCKED = 'Unlocked';
    const TIMESTAMP_KEY = '_fixedTimestamp';

    private string $proxyBase;
    private string $targetServiceUrl;
    private string $cacheMode;
    private string $testName;
    private array $generatedIds = [];
    private int $fixedTimestampMillis;
    private string $language = 'php';
    private ?string $category;
    private ?string $subcategory;
    private ?string $currentFunction;
    private array $variableFields; // ['payload' => [], 'query' => []]

    public function __construct(string $targetServiceUrl, string $cacheMode, string $testName)
    {
        $proxyHost = getenv('PROXY_ENDPOINT');

        if ($proxyHost === false || $proxyHost === '') {
            throw new \RuntimeException(
                'PROXY_ENDPOINT environment variable is not set. '
                    . 'Set it to the proxy hostname (e.g. PROXY_ENDPOINT="your-proxy-host.example.com").'
            );
        }

        $this->proxyBase = 'https://' . $proxyHost . '/proxy';
        $this->targetServiceUrl = $targetServiceUrl;
        $this->cacheMode = $cacheMode;
        $this->testName = $testName;
        $this->variableFields = $this->defaultVariableFieldsFor($targetServiceUrl);

        if ($cacheMode === self::LOCKED) {
            $this->generatedIds = $this->loadIds(true);
        } else {
            $this->generatedIds = $this->loadIds(false);
            if (!array_key_exists(self::TIMESTAMP_KEY, $this->generatedIds)) {
                $this->generatedIds[self::TIMESTAMP_KEY] = (string) (int) round(microtime(true) * 1000);
                $this->writeIds();
            }
        }

        $this->fixedTimestampMillis = (int) $this->generatedIds[self::TIMESTAMP_KEY];

        putenv('SDK_TESTING_TIMESTAMP=' . $this->fixedTimestampMillis);

        date_default_timezone_set('UTC');
    }

    public function setFunction(?string $functionPath): void
    {
        if ($functionPath === null) {
            $this->category = $this->subcategory = $this->currentFunction = null;
            return;
        }

        $parts = explode('|', $functionPath, 3);

        if (count($parts) === 3) {
            [$this->category, $this->subcategory, $this->currentFunction] = $parts;
        } else {
            $this->category = $this->subcategory = null;
            $this->currentFunction = $functionPath;
        }
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * Reset the harness fields so a stale function tag does not leak from one test
     * method into the next. Call this after the test URL has been consumed.
     */
    public function reset(): void
    {
        $this->category = null;
        $this->subcategory = null;
        $this->currentFunction = null;
    }

    public function setVariableFields(array $payload, array $query = []): void
    {
        $this->variableFields = ['payload' => $payload, 'query' => $query];
    }

    public function getTestingUrl(): string
    {
        $cacheReturns = $this->cacheMode === self::LOCKED ? 'true' : 'false';
        $targetHost = preg_replace('#^https?://#', 'https:/', $this->targetServiceUrl);
        $args = 'cacheReturns:' . $cacheReturns;
        $varsParts = [];

        foreach ($this->variableFields['payload'] as $f) {
            $varsParts[] = 'payload.' . $f;
        }

        foreach ($this->variableFields['query'] as $f) {
            $varsParts[] = 'query.' . $f;
        }

        if (!empty($varsParts)) {
            $args .= ',vars:' . implode('|', $varsParts);
        }

        if (
            $this->language !== null && $this->category !== null
            && $this->subcategory !== null && $this->currentFunction !== null
        ) {
            $args .= ',language:' . $this->encode($this->language)
                . ',category:' . $this->encode($this->category)
                . ',subcategory:' . $this->encode($this->subcategory)
                . ',function:' . $this->encode($this->currentFunction);
        }

        return $this->proxyBase . '/(' . $args . ')/' . $targetHost;
    }

    public function getCurrentTime(): \DateTime
    {
        $seconds = intdiv($this->fixedTimestampMillis, 1000);
        $dt = new \DateTime('@' . $seconds);
        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt;
    }

    public function generateRandomId(string $key): string
    {
        if (array_key_exists($key, $this->generatedIds)) {
            return $this->generatedIds[$key];
        }

        if ($this->cacheMode === self::LOCKED) {
            throw new \RuntimeException(
                "Harness is locked but no cached ID found for key: $key. "
                    . 'Run tests in Unlocked mode first to generate IDs.'
            );
        }

        $id = (string) random_int(1000000, 99999998);
        $this->generatedIds[$key] = $id;
        $this->writeIds();
        return $id;
    }

    public function generateRandomDecimal(string $key, float $min, float $max, int $scale): string
    {
        if (array_key_exists($key, $this->generatedIds)) {
            return $this->generatedIds[$key];
        }

        if ($this->cacheMode === self::LOCKED) {
            throw new \RuntimeException(
                "Harness is locked but no cached value found for key: $key. "
                    . 'Run tests in Unlocked mode first to generate values.'
            );
        }

        $val = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
        $result = number_format($val, $scale, '.', '');
        $this->generatedIds[$key] = $result;
        $this->writeIds();
        return $result;
    }

    public function createRequestIdProvider(string $key): IRequestIdProvider
    {
        return new class($this, $key) implements IRequestIdProvider {
            private CiTestingHarness $harness;
            private string $key;
            private int $counter = 0;

            public function __construct(CiTestingHarness $harness, string $key)
            {
                $this->harness = $harness;
                $this->key = $key;
            }

            public function getRequestId(): int
            {
                return (int) $this->harness->generateRandomId($this->key . '_' . $this->counter++);
            }
        };
    }

    private function defaultVariableFieldsFor(string $_targetServiceUrl): array
    {
        return ['payload' => [], 'query' => []];
    }

    private function encode(string $value): string
    {
        return rawurlencode($value); // space -> %20, matches Java URLEncoder + replace
    }

    private function resourceDir(): string
    {
        return __DIR__ . '/../resources/citesting';
    }

    private function writeIds(): void
    {
        $dir = $this->resourceDir();

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(
            $dir . '/' . $this->testName . '.json',
            json_encode($this->generatedIds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function loadIds(bool $required): array
    {
        $file = $this->resourceDir() . '/' . $this->testName . '.json';

        if (!file_exists($file)) {
            if ($required) {
                throw new \RuntimeException(
                    "Harness is locked but ID file not found: $file. "
                        . 'Run tests in Unlocked mode first to generate the file.'
                );
            }

            return [];
        }

        $decoded = json_decode(file_get_contents($file), true);
        return is_array($decoded) ? $decoded : [];
    }
}
