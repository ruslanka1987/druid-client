<?php
declare(strict_types=1);

namespace Level23\Druid\OrderBy;

interface OrderByInterface
{
    /**
     * Return the order by in array format so that it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array;

    /**
     * The dimension where we should order on.
     *
     * @return string
     */
    public function getDimension(): string;

    /**
     * Return the direction of the order by
     *
     * @return string
     */
    public function getDirection(): string;
}
