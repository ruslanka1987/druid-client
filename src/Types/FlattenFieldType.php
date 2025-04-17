<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class FlattenFieldType
 *
 * @package Level23\Druid\Types
 */
class FlattenFieldType extends Enum
{
    public const ROOT = 'root';
    public const PATH = 'path';
    public const JQ = 'jq';

    /**
     * @param string $value
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($value)
    {
        if (!FlattenFieldType::isValidValue($value)) {
            throw new InvalidArgumentException(
                'The given FlattenFieldType value is invalid: ' . $value . '. ' .
                'Allowed are: ' . implode(',', FlattenFieldType::values())
            );
        }

        return $value;
    }
}