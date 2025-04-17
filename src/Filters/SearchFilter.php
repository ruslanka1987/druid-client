<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class SearchFilter
 *
 * Search filters can be used to filter on partial string matches.
 *
 * @package Level23\Druid\Filters
 */
class SearchFilter implements FilterInterface
{
    protected $dimension;

    /**
     * @var string|string[]
     */
    protected $value;

    protected $caseSensitive;

    /**
     * SearchFilter constructor.
     *
     * When an array of values is given, we expect the dimension value contains all
     * the values specified in this search query spec.
     *
     * @param string          $dimension
     * @param string|string[] $valueOrValues
     * @param bool            $caseSensitive
     */
    public function __construct(
        string $dimension,
        $valueOrValues,
        bool $caseSensitive = false
    ) {
        $this->dimension     = $dimension;
        $this->value         = $valueOrValues;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        if (is_array($this->value)) {
            $query = [
                'type'          => 'fragment',
                'values'        => $this->value,
                'caseSensitive' => $this->caseSensitive,
            ];
        } else {
            $query = [
                'type'          => 'contains',
                'value'         => $this->value,
                'caseSensitive' => $this->caseSensitive,
            ];
        }

        return [
            'type'      => 'search',
            'dimension' => $this->dimension,
            'query'     => $query,
        ];
    }
}