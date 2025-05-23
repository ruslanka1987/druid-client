<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Collections\IntervalCollection;

class UniformGranularity extends AbstractGranularity implements GranularityInterface
{
    protected $segmentGranularity;

    /**
     * UniformGranularity constructor.
     *
     * @param string|Granularity $segmentGranularity
     * @param string|Granularity $queryGranularity
     * @param bool               $rollup
     * @param IntervalCollection $intervals
     */
    public function __construct(
        $segmentGranularity,
        $queryGranularity,
        bool $rollup,
        IntervalCollection $intervals
    ) {
        parent::__construct($queryGranularity, $rollup, $intervals);

        $this->segmentGranularity = is_string($segmentGranularity) ? Granularity::from(strtolower($segmentGranularity)) : $segmentGranularity;
    }

    /**
     * Return the granularity in array format so that we can use it in a druid request.
     *
     * @return array<string,string|string[]|bool>
     */
    public function toArray(): array
    {
        return [
            'type'               => 'uniform',
            'segmentGranularity' => is_object($this->segmentGranularity) ? $this->segmentGranularity->value : $this->segmentGranularity,
            'queryGranularity'   => is_object($this->queryGranularity) ? $this->queryGranularity->value : $this->queryGranularity,
            'rollup'             => $this->rollup,
            'intervals'          => $this->intervals->toArray(),
        ];
    }
}