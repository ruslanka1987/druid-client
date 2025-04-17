<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Tasks;

use Mockery;
use ValueError;
use Mockery\MockInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Dimensions\TimestampSpec;
use Level23\Druid\Types\MultiValueHandling;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Dimensions\SpatialDimension;
use Level23\Druid\InputSources\HttpInputSource;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\InputSources\LocalInputSource;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\InputSources\InputSourceInterface;
use Level23\Druid\Granularities\ArbitraryGranularity;
use Level23\Druid\Granularities\GranularityInterface;
use Level23\Druid\Collections\SpatialDimensionCollection;

class IndexTaskBuilderTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $withInputSource
     *
     * @throws \ReflectionException
     */
    public function testConstructor(bool $withInputSource): void
    {
        if ($withInputSource) {
            $inputSource = new HttpInputSource(['http://127.0.0.1/file.json']);
        } else {
            $inputSource = null;
        }

        $client     = new DruidClient([]);
        $dataSource = 'people';
        $builder    = new IndexTaskBuilder($client, $dataSource, $inputSource);

        $this->assertFalse(
            $this->getProperty($builder, 'appendToExisting')
        );
        $this->assertEquals($builder, $builder->appendToExisting());

        $this->assertTrue(
            $this->getProperty($builder, 'appendToExisting')
        );

        $this->assertFalse(
            $this->getProperty($builder, 'rollup')
        );
        $this->assertEquals($builder, $builder->rollup());
        $this->assertTrue(
            $this->getProperty($builder, 'rollup')
        );

        $this->assertEquals(
            $dataSource,
            $this->getProperty($builder, 'dataSource')
        );

        $this->assertEquals(
            $client,
            $this->getProperty($builder, 'client')
        );

        $this->assertEquals(
            $inputSource,
            $this->getProperty($builder, 'inputSource')
        );

        $this->assertEquals(
            UniformGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );
        $this->assertEquals($builder, $builder->arbitraryGranularity());
        $this->assertEquals(
            ArbitraryGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );

        $this->assertEquals($builder, $builder->uniformGranularity());
        $this->assertEquals(
            UniformGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testParallel(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'wikipedia');

        $this->assertFalse($this->getProperty($builder, 'parallel'));

        $builder->parallel();

        $this->assertTrue($this->getProperty($builder, 'parallel'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSpatialDimension(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $spatialDimension = $this->getConstructorMock(SpatialDimension::class);
        $spatialDimension->shouldReceive('__construct')
            ->once()
            ->with('location', ['lat', 'long']);

        $this->assertEquals(
            $builder,
            $builder->spatialDimension('location', ['lat', 'long'])
        );
    }

    /**
     * @testWith ["String", "array", true]
     *           ["DOUBLE", "sorted_array", false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \ReflectionException
     */
    public function testMultiValueDimension(string $type, string $multiValueHandling, bool $createBitmapIndex): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'coordinates');

        $this->assertEquals(
            $builder,
            $builder->multiValueDimension('position', $type, $multiValueHandling, $createBitmapIndex)
        );

        $this->assertEquals([
            [
                'name'               => 'position',
                'type'               => DataType::from(strtolower($type))->value,
                'multiValueHandling' => MultiValueHandling::from(strtoupper($multiValueHandling))->value,
                'createBitmapIndex'  => $createBitmapIndex,
            ],
        ], $this->getProperty($builder, 'dimensions'));
    }

    /**
     * @testWith ["SORTED_ARRAY", false]
     *           ["SORTED_SET", false]
     *           ["ARRAY", false]
     *           ["Array", false]
     *           ["ArRaY", false]
     *           ["LefT", true]
     *           ["aray", true]
     *           [" array ", true]
     *           ["sortedSet", true]
     *           ["sortedArray", true]
     *
     * @param string $value
     * @param bool   $expectException
     *
     * @return void
     */
    public function testMultiValueHandling(string $value, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage(
                '"' . strtoupper($value) . '" is not a valid backing value for enum Level23\Druid\Types\MultiValueHandling'

            );
        }

        $this->assertEquals(strtoupper($value), MultiValueHandling::from(strtoupper($value))->value);
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $withTransform
     *
     * @throws \ReflectionException
     */
    public function testTransformBuilder(bool $withTransform): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'animals';
        $builder    = new IndexTaskBuilder($client, $dataSource);

        $counter  = 0;
        $function = function ($builder) use (&$counter, $withTransform) {
            $this->assertInstanceOf(TransformBuilder::class, $builder);
            $counter++;

            if ($withTransform) {
                $builder->transform('concat(foo, bar)', 'fooBar');
            }
        };

        $response = $builder->transform($function);

        $this->assertEquals($builder, $response);

        $this->assertEquals(1, $counter);

        if ($withTransform) {
            /** @var TransformSpec $transformSpec */
            $transformSpec = $this->getProperty($builder, 'transformSpec');
            $this->assertInstanceOf(
                TransformSpec::class,
                $transformSpec
            );

            $this->assertEquals([
                'transforms' => [
                    [
                        'type'       => 'expression',
                        'name'       => 'fooBar',
                        'expression' => 'concat(foo, bar)',
                    ],
                ],
            ], $transformSpec->toArray());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testDimension(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'aliens';
        $builder    = new IndexTaskBuilder($client, $dataSource);

        $builder->dimension('name', 'STRING');
        $builder->dimension('age', 'LoNg');

        $this->assertEquals([
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'age', 'type' => 'long'],
        ], $this->getProperty($builder, 'dimensions'));
    }

    /**
     * @return array<array<string|Interval|null|InputSourceInterface>>
     */
    public static function buildTaskDataProvider(): array
    {
        return [
            ["day", "week", new Interval("12-02-2019/13-02-2019"), new DruidInputSource('mySource')],
            ["day", "hour", new Interval("12-02-2019/13-02-2019"), new HttpInputSource(['http://127.0.0.1/test.json'])],
            ["day", "day", new Interval("12-02-2019/13-02-2019"), null],
            ["day", "day", new Interval("12-02-2019/13-02-2019"), new LocalInputSource(["/path/to/file.json"])],

        ];
    }

    /**
     * @param string                                                $queryGranularity
     * @param string                                                $segmentGranularity
     * @param \Level23\Druid\Interval\Interval                      $interval
     * @param \Level23\Druid\InputSources\InputSourceInterface|null $inputSource
     *
     * @throws \ReflectionException
     * @throws \Exception
     *
     * @dataProvider        buildTaskDataProvider
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBuildTask(
        string $queryGranularity,
        string $segmentGranularity,
        Interval $interval,
        ?InputSourceInterface $inputSource
    ): void {
        $context    = [];
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource, $inputSource]);
        $builder->makePartial();

        $this->assertEquals($inputSource, $this->getProperty($builder, 'inputSource'));

        $builder->queryGranularity($queryGranularity);
        $builder->segmentGranularity($segmentGranularity);
        $builder->interval($interval->getStart(), $interval->getStop());
        $builder->parallel();
        $builder->appendToExisting();
        $builder->timestamp('datetime', 'auto');

        if ($inputSource) {
            $mock = new Mockery\Generator\MockConfigurationBuilder();
            $mock->setInstanceMock(true);
            $mock->setName(IndexTask::class);
            $mock->addTarget(TaskInterface::class);

            $indexTask = Mockery::mock($mock);

            $indexTask
                ->shouldReceive('__construct')
                ->once()
                ->withArgs(function (
                    $givenDateSource,
                    $givenInputSource,
                    $granularity,
                    $timestampSpec,
                    $transformSpec,
                    $tuningConfig,
                    $context,
                    $aggregations,
                    $dimensions,
                    $taskId,
                    $inputFormat,
                    $spatialDimensions
                ) use ($inputSource, $dataSource) {
                    $this->assertEquals($dataSource, $givenDateSource);
                    $this->assertEquals($inputSource, $givenInputSource);
                    $this->assertInstanceOf(GranularityInterface::class, $granularity);
                    $this->assertInstanceOf(TimestampSpec::class, $timestampSpec);
                    $this->assertNull($transformSpec);
                    $this->assertNull($tuningConfig);
                    $this->assertInstanceOf(TaskContext::class, $context);
                    $this->assertInstanceOf(AggregationCollection::class, $aggregations);
                    $this->assertIsArray($dimensions);
                    $this->assertNull($taskId);
                    $this->assertNull($inputFormat);
                    $this->assertInstanceOf(SpatialDimensionCollection::class, $spatialDimensions);

                    return true;
                });

            $indexTask->shouldReceive('setParallel')
                ->once()
                ->with(true);

            $indexTask->shouldReceive('setAppendToExisting')
                ->once()
                ->with(true);
        } else {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('No InputSource known. You have to supply an input source!');
        }

        $builder->shouldAllowMockingProtectedMethods()->buildTask($context);
    }

    /**
     * @throws \Exception
     */
    public function testBuildTaskWithoutTimestampSpec(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();
        $builder->queryGranularity(Granularity::HOUR);
        $builder->segmentGranularity(Granularity::HOUR);
        $builder->interval('now - 1 week/now');
        $builder->inputSource(new HttpInputSource(['http://127.0.0.1/file.json']));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify an timestamp column!');

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    public function testBuildTaskWithoutQueryGranularity(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify a queryGranularity value!');

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    public function testBuildTaskWithoutInterval(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify an interval!');

        $builder->queryGranularity('day');

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @throws \Exception
     */
    public function testBuildTaskWithoutSegmentGranularity(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify a segmentGranularity value!');

        $builder->queryGranularity('day');
        $builder->timestamp('timestamp', 'auto');
        $builder->interval('12-02-2019', '13-02-2019');
        $builder->uniformGranularity();

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @throws \Exception
     */
    public function testBuildTaskForDruidInputSourceWithoutInterval(): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'phones';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $inputSource = Mockery::mock(DruidInputSource::class, ['old_phones']);

        $builder->queryGranularity('day');
        $builder->segmentGranularity('day');
        $builder->timestamp('timestamp', 'auto');
        $builder->interval('12-02-2019', '13-02-2019');
        $builder->inputSource($inputSource);

        $inputSource->shouldReceive('getInterval')
            ->once()
            ->andReturnNull();

        $inputSource->shouldReceive('setInterval')
            ->once()
            ->withArgs(function (Interval $interval) {
                $this->assertEquals('12-02-2019', $interval->getStart()->format('d-m-Y'));
                $this->assertEquals('13-02-2019', $interval->getStop()->format('d-m-Y'));

                return true;
            });

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @testWith ["Level23\\Druid\\Granularities\\UniformGranularity"]
     *           ["Level23\\Druid\\Granularities\\ArbitraryGranularity"]
     *
     * @param string $granularityType
     *
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     */
    public function testBuildTaskGranularityObject(string $granularityType): void
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $builder->queryGranularity('day');
        $builder->segmentGranularity('week');
        $builder->timestamp('timestamp', 'auto');
        $builder->interval('12-02-2019', '13-02-2019');

        if ($granularityType == ArbitraryGranularity::class) {
            $builder->arbitraryGranularity();

            $this->getGranularityMock(ArbitraryGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    Granularity::DAY,
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        } else {
            $builder->uniformGranularity();

            $this->getGranularityMock(UniformGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    Granularity::WEEK,
                    Granularity::DAY,
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No InputSource known.');

        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @param string $class
     *
     * @return LegacyMockInterface|MockInterface
     */
    protected function getGranularityMock(string $class): LegacyMockInterface|MockInterface
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(GranularityInterface::class);

        return Mockery::mock($builder);
    }
}
