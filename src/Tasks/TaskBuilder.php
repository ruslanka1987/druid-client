<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Interval\IntervalInterface;
use function json_encode;

abstract class TaskBuilder
{
    /**
     * @var DruidClient
     */
    protected DruidClient $client;

    /**
     * The task ID. If this is not explicitly specified, Druid generates the task ID using task type,
     * data source name, interval, and date-time stamp.
     *
     * @var string|null
     */
    protected ?string $taskId = null;

    /**
     * Check if the given interval is valid for the given dataSource.
     *
     * @param string                                    $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface $interval
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function validateInterval(string $dataSource, IntervalInterface $interval): void
    {
        $fromStr = $interval->getStart()->format('Y-m-d\TH:i:s.000\Z');
        $toStr   = $interval->getStop()->format('Y-m-d\TH:i:s.000\Z');

        $foundFrom = false;
        $foundTo   = false;

        // Get all intervals and check if our interval is among them.
        $intervals = array_keys($this->client->metadata()->intervals($dataSource));

        foreach ($intervals as $dateStr) {

            if (!$foundFrom && str_starts_with($dateStr, $fromStr)) {
                $foundFrom = true;
            }

            if (!$foundTo && str_ends_with($dateStr, $toStr)) {
                $foundTo = true;
            }

            if ($foundFrom && $foundTo) {
                return;
            }
        }

        throw new InvalidArgumentException(
            'Error, invalid interval given. The given dates do not match a complete interval!' . PHP_EOL .
            'Given interval: ' . $interval->getInterval() . PHP_EOL .
            'Valid intervals: ' . implode(', ', $intervals)
        );
    }

    /**
     * Execute the index task. We will return the task identifier.
     *
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function execute(array|TaskContext $context = []): string
    {
        $task = $this->buildTask($context);

        return $this->client->executeTask($task);
    }

    /**
     * Return the task in Json format.
     *
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \JsonException
     */
    public function toJson(array|TaskContext $context = []): string
    {
        $task = $this->buildTask($context);

        $json = json_encode($task->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return (string)$json;
    }

    /**
     * Return the task as array
     *
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return array<string,mixed>
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function toArray(array|TaskContext $context = []): array
    {
        $task = $this->buildTask($context);

        return $task->toArray();
    }

    /**
     * The task ID. If this is not explicitly specified, Druid generates the task ID using task type,
     * data source name, interval, and date-time stamp.
     *
     * @param string $taskId
     *
     * @return $this
     */
    public function taskId(string $taskId): self
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return \Level23\Druid\Tasks\TaskInterface
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    abstract protected function buildTask(array|TaskContext $context): TaskInterface;
}