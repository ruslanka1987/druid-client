<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

/**
 * @internal
 */
class MapLookup implements LookupInterface
{
    protected $map;

    /**
     * @param array<int|string|float,int|string|float> $map
     */
    public function __construct($map)
    {
        $this->map = $map;
    }

    public function toArray(): array
    {
        return [
            'type' => 'map',
            'map'  => $this->map,
        ];
    }
}