<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class TsvParseSpec implements ParseSpecInterface
{
    protected $columns;
    protected $keyColumn = null;
    protected $valueColumn = null;
    protected $delimiter = null;
    protected $listDelimiter = null;
    protected $hasHeaderRow = false;
    protected $skipHeaderRows = 0;

    /**
     * Specify the TSV parse spec.
     *
     * @param array<int,string>|null $columns
     * @param string|null $keyColumn
     * @param string|null $valueColumn
     * @param string|null $delimiter
     * @param string|null $listDelimiter
     * @param bool $hasHeaderRow
     * @param int $skipHeaderRows
     */
    public function __construct(
        $columns,
        $keyColumn = null,
        $valueColumn = null,
        $delimiter = null,
        $listDelimiter = null,
        $hasHeaderRow = false,
        $skipHeaderRows = 0
    )
    {
        $this->columns = $columns;
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
        $this->delimiter = $delimiter;
        $this->listDelimiter = $listDelimiter;
        $this->hasHeaderRow = $hasHeaderRow;
        $this->skipHeaderRows = $skipHeaderRows;
    }

    /**
     * @return array<string,bool|array<int,string>|string|int|null>
     */
    public function toArray(): array
    {
        $response = [
            'format' => 'tsv',
            'columns' => $this->columns,
            'hasHeaderRow' => $this->hasHeaderRow,
        ];

        if ($this->keyColumn !== null) {
            $response['keyColumn'] = $this->keyColumn;
        }
        if ($this->valueColumn !== null) {
            $response['valueColumn'] = $this->valueColumn;
        }
        if ($this->delimiter !== null) {
            $response['delimiter'] = $this->delimiter;
        }
        if ($this->listDelimiter !== null) {
            $response['listDelimiter'] = $this->listDelimiter;
        }
        $response['skipHeaderRows'] = $this->skipHeaderRows;

        return $response;
    }
}