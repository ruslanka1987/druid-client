<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\Types\ArithmeticFunction;
use Level23\Druid\Collections\PostAggregationCollection;

class ArithmeticPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected ArithmeticFunction $function;

    protected PostAggregationCollection $fields;

    protected bool $floatingPointOrdering;

    /**
     * ArithmeticPostAggregator constructor.
     *
     * The arithmetic post-aggregator applies the provided function to the given fields from left to right. The fields
     * can be aggregators or other post aggregators.
     *
     * Notes:
     * -  / division always returns 0 if dividing by 0, regardless of the numerator.
     * - quotient division behaves like regular floating point division
     *
     * @param string                    $outputName
     * @param string|ArithmeticFunction                    $function              Supported functions are +, -, *, /, and quotient.
     * @param PostAggregationCollection $fields                List with field names which are used for this function.
     *
     *
     * @param bool                      $floatingPointOrdering By default, floating point ordering is used. When set to
     *                                                         false we will use numericFirst ordering. It returns
     *                                                         finite values first,followed by NaN, and infinite values
     *                                                         last.
     */
    public function __construct(
        string $outputName,
        string|ArithmeticFunction $function,
        PostAggregationCollection $fields,
        bool $floatingPointOrdering = true
    ) {
        $this->outputName            = $outputName;
        $this->function              = is_string($function) ? ArithmeticFunction::from(strtolower($function)) : $function;
        $this->fields                = $fields;
        $this->floatingPointOrdering = $floatingPointOrdering;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string|array<mixed>>>|null>
     */
    public function toArray(): array
    {
        return [
            'type'     => 'arithmetic',
            'name'     => $this->outputName,
            'fn'       => $this->function->value,
            'fields'   => $this->fields->toArray(),
            'ordering' => $this->floatingPointOrdering ? null : 'numericFirst',
        ];
    }
}