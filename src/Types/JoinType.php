<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class NullHandling
 *
 * @package Level23\Druid\Types
 */
class JoinType extends Enum
{
    public const INNER = 'INNER';
    public const LEFT = 'LEFT';

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