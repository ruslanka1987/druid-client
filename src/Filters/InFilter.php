<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class InFilter implements FilterInterface
{
    protected $dimension;

    /**
     * @var string[]|int[]
     */
    protected $values;

    /**
     * InFilter constructor.
     *
     * @param string                   $dimension
     * @param string[]|int[]           $values
     */
    public function __construct(string $dimension, array $values)
    {
        $this->values     = $values;
        $this->dimension  = $dimension;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<int|string>|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'in',
            'dimension' => $this->dimension,
            'values'    => array_values($this->values),
        ];
    }
}