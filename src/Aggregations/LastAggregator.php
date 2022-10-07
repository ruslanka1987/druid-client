<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class LastAggregator extends MethodAggregator
{
    
    public function __construct(string $metricName, string $outputName = '', string $type = 'long')
    {
        $this->type       = $type;
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;
    }
    
    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'last';
    }
}
