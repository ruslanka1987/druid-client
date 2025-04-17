<?php

declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class SortingOrder
 *
 * @package Level23\Druid\Types
 */
final class TimeBound extends Enum
{
    public const MAX_TIME = 'maxTime';
    public const MIN_TIME = 'minTime';
    public const BOTH = 'both';

    /**
     * @param string $ordering
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($ordering)
    {
        $ordering = strtolower($ordering);

        if (!TimeBound::isValidValue($ordering)) {
            throw new InvalidArgumentException(
                'The given sorting order is invalid: ' . $ordering . '. ' .
                'Allowed are: ' . implode(',', TimeBound::values())
            );
        }

        return $ordering;
    }
}