<?php
declare(strict_types=1);

namespace Level23\Druid\Metadata;

class Structure
{
    public $dataSource;

    /**
     * A list of the dimensions and their associated type.
     *
     * @var array<string,string>
     */
    public $dimensions = [];

    /**
     * A list of the metrics and their associated type.
     *
     * @var array<string,string>
     */
    public $metrics = [];

    /**
     * @param string               $dataSource
     * @param array<string,string> $dimensions
     * @param array<string,string> $metrics
     */
    public function __construct(string $dataSource, array $dimensions, array $metrics)
    {
        $this->dataSource = $dataSource;
        $this->dimensions = $dimensions;
        $this->metrics    = $metrics;
    }
}