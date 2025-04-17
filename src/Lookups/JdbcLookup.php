<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

/**
 * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global#jdbc-lookup
 * @internal
 */
class JdbcLookup implements LookupInterface
{
    protected $connectUri;
        protected $username;
        protected $password;
        protected $table;
        protected $keyColumn;
        protected $valueColumn;
        protected $filter = null;
        protected $tsColumn = null;
        protected $jitterSeconds = null;
        protected $loadTimeoutSeconds = null;
        protected $pollPeriod = null;
        protected $maxHeapPercentage = null;
        protected $injective = false;
        protected $firstCacheTimeoutMs = 0;

    /**
     * The JDBC lookups will poll a database to populate its local cache. If the tsColumn is set it must be able to
     * accept comparisons in the format '2015-01-01 00:00:00'. For example, the following must be valid SQL for the
     * table SELECT * FROM some_lookup_table WHERE timestamp_column > '2015-01-01 00:00:00'. If tsColumn is set, the
     * caching service will attempt to only poll values that were written after the last sync. If tsColumn is not set,
     * the entire table is pulled every time.
     *
     * @see https://druid.apache.org/docs/latest/configuration/#jdbc-connections-to-external-databases
     *
     * @param string          $connectUri          The JDBC connect Uri. You can selectively allow JDBC properties in
     *                                             connectURI. See JDBC connections security config for more details.
     * @param string|null     $username
     * @param string|null     $password
     * @param string          $table               The table which contains the key value pairs
     * @param string          $keyColumn           The column in table which contains the keys
     * @param string          $valueColumn         The column in table which contains the values
     * @param string|null     $filter              The filter to use when selecting lookups, this is used to create a
     *                                             where clause on lookup population. For example "age >= 18"
     * @param string|null     $tsColumn            The column in table which contains when the key was updated
     * @param int|null        $jitterSeconds       How much jitter to add (in seconds) up to maximum as a delay (actual
     *                                             value will be used as random from 0 to jitterSeconds), used to
     *                                             distribute db load more evenly
     * @param int|null        $loadTimeoutSeconds  How much time (in seconds) it can take to query and populate lookup
     *                                             values. It will be helpful in lookup updates. On lookup update, it
     *                                             will wait maximum of loadTimeoutSeconds for new lookup to come up
     *                                             and continue serving from old lookup until new lookup successfully
     *                                             loads.
     * @param string|int|null $pollPeriod          The pollPeriod value specifies the period in ISO 8601 format between
     *                                             checks for replacement data for the lookup. For example PT15M. When
     *                                             not given, it is only once.
     * @param int|null        $maxHeapPercentage   The maximum percentage of heap size that the lookup should consume.
     *                                             If the lookup grows beyond this size, warning messages will be
     *                                             logged in the  respective service logs.
     * @param bool            $injective           If the underlying map is injective (keys and values are unique) then
     *                                             optimizations can occur internally by setting this to true
     * @param int             $firstCacheTimeoutMs How long to wait (in ms) for the first run of the cache to populate.
     *                                             0 indicates to not  wait
     */
    public function __construct(
        $connectUri,
        $username,
        $password,
        $table,
        $keyColumn,
        $valueColumn,
        $filter = null,
        $tsColumn = null,
        $jitterSeconds = null,
        $loadTimeoutSeconds = null,
        $pollPeriod = null,
        $maxHeapPercentage = null,
        $injective = false,
        $firstCacheTimeoutMs = 0
    ) {
        $this->connectUri = $connectUri;
        $this->username   = $username;
        $this->password   = $password;
        $this->table      = $table;
        $this->keyColumn  = $keyColumn;
        $this->valueColumn = $valueColumn;
        $this->filter     = $filter;
        $this->tsColumn   = $tsColumn;
        $this->jitterSeconds = $jitterSeconds;
        $this->loadTimeoutSeconds = $loadTimeoutSeconds;
        $this->pollPeriod   = $pollPeriod;
        $this->maxHeapPercentage = $maxHeapPercentage;
        $this->injective     = $injective;
        $this->firstCacheTimeoutMs = $firstCacheTimeoutMs;
    }

    public function toArray(): array
    {
        $response = [
            'type'            => 'jdbc',
            'connectorConfig' => [
                'connectURI' => $this->connectUri,
            ],
            'table'           => $this->table,
            'keyColumn'       => $this->keyColumn,
            'valueColumn'     => $this->valueColumn,
        ];

        if ($this->username !== null) {
            $response['connectorConfig']['user'] = $this->username;
        }
        if ($this->password !== null) {
            $response['connectorConfig']['password'] = $this->password;
        }

        if ($this->filter !== null) {
            $response['filter'] = $this->filter;
        }

        if ($this->tsColumn) {
            $response['tsColumn'] = $this->tsColumn;
        }

        if ($this->pollPeriod !== null) {
            $response['pollPeriod'] = $this->pollPeriod;
        }

        if ($this->jitterSeconds !== null) {
            $response['jitterSeconds'] = $this->jitterSeconds;
        }

        if ($this->loadTimeoutSeconds !== null) {
            $response['loadTimeoutSeconds'] = $this->loadTimeoutSeconds;
        }

        if ($this->maxHeapPercentage !== null) {
            $response['maxHeapPercentage'] = $this->maxHeapPercentage;
        }

        return [
            'type'                => 'cachedNamespace',
            'extractionNamespace' => $response,
            'injective'           => $this->injective,
            'firstCacheTimeout'   => $this->firstCacheTimeoutMs,
        ];
    }
}