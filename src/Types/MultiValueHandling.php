<?php

declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class MultiValueHandling
 *
 * @package Level23\Druid\Types
 */
class MultiValueHandling extends Enum
{
    public const SORTED_ARRAY = 'SORTED_ARRAY';
    public const SORTED_SET = 'SORTED_SET';
    public const ARRAY = 'ARRAY';

    /**
     * @param string $nullHandling
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($nullHandling)
    {
        if (!MultiValueHandling::isValidValue($nullHandling)) {
            throw new InvalidArgumentException(
                'The given NullHandling value is invalid: ' . $nullHandling . '. ' .
                'Allowed are: ' . implode(',', MultiValueHandling::values())
            );
        }

        return $nullHandling;
    }
}