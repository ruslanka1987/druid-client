<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class CsvParseSpec implements ParseSpecInterface
{
    protected $columns;
    protected $keyColumn = null;
    protected $valueColumn = null;
    protected $hasHeaderRow = false;
    protected $skipHeaderRows = 0;

    public function __construct($columns, $keyColumn = null, $valueColumn = null, $hasHeaderRow = false, $skipHeaderRows = 0)
    {
        $this->columns = $columns;
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
        $this->hasHeaderRow = $hasHeaderRow;
        $this->skipHeaderRows = $skipHeaderRows;
    }

    /**
     * @return array<string,bool|array<int,string>|string|int>
     */
    public function toArray(): array
    {
        $response = [
            'format' => 'csv',
            'hasHeaderRow' => $this->hasHeaderRow,
        ];

        if ($this->columns !== null) {
            $response['columns'] = $this->columns;
        }

        if ($this->keyColumn !== null) {
            $response['keyColumn'] = $this->keyColumn;
        }
        if ($this->valueColumn !== null) {
            $response['valueColumn'] = $this->valueColumn;
        }
        $response['skipHeaderRows'] = $this->skipHeaderRows;

        return $response;
    }
}