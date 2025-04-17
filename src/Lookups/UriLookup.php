<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

use Level23\Druid\Lookups\ParseSpecs\ParseSpecInterface;

/**
 * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global#uri-lookup
 * @internal
 */
class UriLookup implements LookupInterface
{
    protected $parseSpec;
    protected $uri;
    protected $pollPeriod = null;
    protected $maxHeapPercentage = null;
    protected $injective = false;
    protected $firstCacheTimeoutMs = 0;

    public function __construct(
        $parseSpec,
        $uri,
        $pollPeriod = null,
        $maxHeapPercentage = null,
        $injective = false,
        $firstCacheTimeoutMs = 0
    )
    {

    }

    public function toArray(): array
    {
        $response = [
            'type' => 'uri',
            'uri' => $this->uri,
            'namespaceParseSpec' => $this->parseSpec->toArray(),
        ];

        if ($this->pollPeriod !== null) {
            $response['pollPeriod'] = $this->pollPeriod;
        }

        if ($this->maxHeapPercentage !== null) {
            $response['maxHeapPercentage'] = $this->maxHeapPercentage;
        }

        return [
            'type' => 'cachedNamespace',
            'extractionNamespace' => $response,
            'injective' => $this->injective,
            'firstCacheTimeout' => $this->firstCacheTimeoutMs,
        ];
    }
}