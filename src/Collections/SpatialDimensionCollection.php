<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Dimensions\SpatialDimension;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<SpatialDimension>
 */
class SpatialDimensionCollection extends BaseCollection
{
    /**
     * SpatialDimensionCollection constructor.
     *
     * @param \Level23\Druid\Dimensions\SpatialDimension ...$dimensions
     */
    public function __construct(SpatialDimension ...$dimensions)
    {
        $this->items = array_values($dimensions);
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return SpatialDimension::class;
    }

    /**
     * Return an array representation of our items
     *
     * @return array<int,array<mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn(SpatialDimension $item) => $item->toArray(), $this->items);
    }
}