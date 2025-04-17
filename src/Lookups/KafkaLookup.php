<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

/**
 * @see https://druid.apache.org/docs/latest/querying/kafka-extraction-namespace
 * @internal
 */
class KafkaLookup implements LookupInterface
{

    protected $kafkaTopic;
    protected $servers;
    protected $kafkaProperties = [];
    protected $connectTimeout = 0;
    protected $isOneToOne = false;

    /**
     * @param string $kafkaTopic The Kafka topic to read the data from
     * @param string|array<int,string> $servers
     * @param array<string,scalar> $kafkaProperties Kafka consumer properties
     * @param int $connectTimeout How long to wait for an initial connection
     * @param bool $isOneToOne The map is a one-to-one (like injective)
     */
    public function __construct(
        $kafkaTopic,
        $servers,
        $kafkaProperties = [],
        $connectTimeout = 0,
        $isOneToOne = false
    )
    {
        $this->kafkaTopic = $kafkaTopic;
        $this->servers = $servers;
        $this->kafkaProperties = $kafkaProperties;
        $this->connectTimeout = $connectTimeout;
        $this->isOneToOne = $isOneToOne;

        $this->kafkaProperties['bootstrap.servers'] =
            is_array($this->servers)
                ? implode(',', $this->servers)
                : $this->servers;
    }

    /**
     * @return array<string,string|array<string,scalar>|int|bool>
     */
    public function toArray(): array
    {
        return [
            'type' => 'kafka',
            'kafkaTopic' => $this->kafkaTopic,
            'kafkaProperties' => $this->kafkaProperties,
            'connectTimeout' => $this->connectTimeout,
            'isOneToOne' => $this->isOneToOne,
        ];
    }
}