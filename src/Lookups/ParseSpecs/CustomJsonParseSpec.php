<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class CustomJsonParseSpec implements ParseSpecInterface
{
    protected $keyFieldName;
    protected $valueFieldName;

    /**
     * Specify the parse specification to be a json file.
     *
     * @param string $keyFieldName The field name of the key
     * @param string $valueFieldName The field name of the value
     */
    public function __construct(
        $keyFieldName, $valueFieldName
    )
    {
        $this->keyFieldName = $keyFieldName;
        $this->valueFieldName = $valueFieldName;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'format' => 'customJson',
            'keyFieldName' => $this->keyFieldName,
            'valueFieldName' => $this->valueFieldName,
        ];
    }
}