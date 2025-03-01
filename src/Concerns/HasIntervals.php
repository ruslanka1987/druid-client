<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Interval\Interval;

trait HasIntervals
{
    /**
     * @var array|\Level23\Druid\Interval\IntervalInterface[]
     */
    protected array $intervals = [];

    /**
     * Add the interval, e.g. the date where we want to select data from.
     *
     * You should specify the interval in string form like "$start/$stop" format, or give two parameters
     * where each parameter should be a DateTime object, unix timestamp or string accepted by DateTime::__construct.
     *
     * All these examples are valid:
     *
     * ```php
     * // Select an interval with string values. Anything which can be parsed by the DateTime object
     * // can be given. Also "yesterday" or "now" is valid.
     * interval('2019-12-23', '2019-12-24');
     *
     * // When a string is given which contains a slash, we will split it for you and parse it as "begin/end".
     * interval('yesterday/now');
     *
     * // An "raw" interval as druid uses them is also allowed
     * interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');
     *
     * // You can also give DateTime objects
     * interval(new DateTime('yesterday'), new DateTime('now'));
     *
     * // Carbon is also supported, as it extends DateTime
     * interval(Carbon::now()->subDay(), Carbon::now());
     *
     * // Timestamps are also supported:
     * interval(1570643085, 1570729485);
     * ```
     *
     * @param \DateTimeInterface|string|int      $start DateTime object, unix timestamp or string accepted by
     *                                                  DateTime::__construct
     * @param \DateTimeInterface|string|int|null $stop  DateTime object, unix timestamp or string accepted by
     *                                                  DateTime::__construct
     *
     * @return $this
     * @throws \Exception
     */
    public function interval($start, $stop = null): self
    {
        $this->intervals[] = new Interval($start, $stop);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\Interval\IntervalInterface[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }
}