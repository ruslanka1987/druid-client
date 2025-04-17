<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\VirtualColumns\VirtualColumnInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<VirtualColumnInterface>
 */
class VirtualColumnCollection extends BaseCollection
{
    public function __construct(VirtualColumnInterface ...$virtualColumns)
    {
        $this->items = array_values($virtualColumns);
    }

    /**
     * Return an array representation of our items
     *
     * @return array<int,array<mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn(VirtualColumnInterface $item) => $item->toArray(), $this->items);
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return VirtualColumnInterface::class;
    }
}