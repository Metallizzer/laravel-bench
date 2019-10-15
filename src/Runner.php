<?php

namespace Metallizzer\Bench;

use Metallizzer\Bench\Benchmarks\AbstractBench;
use ReflectionMethod;

class Runner
{
    /**
     * List of methods for the selected benchmark.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The benchmark instance.
     *
     * @var AbstractBench
     */
    protected $benchmark;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var float
     */
    protected $memory;

    /**
     * Statistics of running benchmarks.
     *
     * @var array
     */
    protected $benchmarks = [];

    /**
     * The fastest method and subject.
     *
     * @var array
     */
    protected $fastest = [];

    /**
     * The slowest method and subject.
     *
     * @var array
     */
    protected $slowest = [];

    /**
     * Create runner instance.
     *
     * @param AbstractBench $benchmark
     */
    public function __construct(AbstractBench $benchmark)
    {
        $this->benchmark = $benchmark;
        $this->methods   = $benchmark->getMethods();
    }

    /**
     * Run the benchmark.
     *
     * @return $this
     */
    public function run()
    {
        $this->benchmarks = $this->fastest = $this->slowest = [];

        $this->benchmark->runBefore();
        event('bench.run_before', $this->benchmark);

        $this->runBenchmarks()->calculatePercentage();

        $this->benchmark->runAfter();
        event('bench.run_after', $this->benchmark);

        return $this;
    }

    /**
     * Get stats of completed benchmarks.
     *
     * @return array
     */
    public function getStats()
    {
        $benchmarks = collect($this->benchmarks);

        return [
            'class'       => get_class($this->benchmark),
            'name'        => $this->benchmark->getName(),
            'description' => $this->benchmark->getDescription(),
            'loops'       => [
                'base'  => $this->benchmark->getLoops(),
                'total' => $this->benchmark->getLoops() * count($this->benchmark->getMethods()) * count($this->benchmark->getSubjects()),
            ],
            'subjects'   => $this->benchmark->getSubjects(),
            'benchmarks' => $this->benchmarks,
            'fastest'    => $this->fastest,
            'slowest'    => $this->slowest,
        ];
    }

    /**
     * Run all benchmark methods.
     *
     * @return $this
     */
    protected function runBenchmarks()
    {
        foreach ($this->methods as $method) {
            event('bench.method_started', [$method]);

            $this->benchmarks[$method] = [
                'time'     => 0,
                'memory'   => 0,
                'subjects' => [],
            ];

            $reflection = new ReflectionMethod(get_class($this->benchmark), $method);

            foreach ($this->benchmark->getSubjects() as $key => $subject) {
                event('bench.subject_started', [$subject, $method]);

                $return = $reflection->invoke($this->benchmark, $subject);

                unset($result);

                $this->start();

                for ($i = 0; $i < $this->benchmark->getLoops(); ++$i) {
                    $result = $reflection->invoke($this->benchmark, $subject);
                }

                $this->stop();

                $this->benchmarks[$method]['subjects'][$key] = [
                    'return' => $return,
                    'time'   => $this->time,
                    'memory' => $this->memory,
                ];

                $this->benchmarks[$method]['time']   += $this->time;
                $this->benchmarks[$method]['memory'] += $this->memory;

                $this->updatePeaks('subject', $this->time, $this->memory);

                event('bench.subject_completed', [$subject, $method]);
            }

            $this->updatePeaks('method', $this->benchmarks[$method]['time'], $this->benchmarks[$method]['memory']);

            event('bench.method_completed', $method);
        }

        return $this;
    }

    /**
     * Calculate the percentage of runtime and used memory of running benchmarks.
     *
     * @return $this
     */
    protected function calculatePercentage()
    {
        foreach ($this->benchmarks as &$method) {
            $this->setPercentage('fastest', 'method', 'time', $method)
                ->setPercentage('fastest', 'method', 'memory', $method)
                ->setPercentage('slowest', 'method', 'time', $method)
                ->setPercentage('slowest', 'method', 'memory', $method);

            $method['grade'] = ($method['time'] - $this->fastest['method']['time']) / ($this->slowest['method']['time'] - $this->fastest['method']['time']);

            foreach ($method['subjects'] as &$subject) {
                $this->setPercentage('fastest', 'subject', 'time', $subject)
                    ->setPercentage('fastest', 'subject', 'memory', $subject)
                    ->setPercentage('slowest', 'subject', 'time', $subject)
                    ->setPercentage('slowest', 'subject', 'memory', $subject);

                $subject['grade'] = ($subject['time'] - $this->fastest['subject']['time']) / ($this->slowest['subject']['time'] - $this->fastest['subject']['time']);
            }
        }

        return $this;
    }

    /**
     * Set the percentage of runtime and used memory of a method or subject.
     *
     * @param string $speed   fastest or slowest
     * @param string $type    method or subject
     * @param string $key     time or memory
     * @param array  &$return
     *
     * @return $this
     */
    protected function setPercentage($speed, $type, $key, &$return)
    {
        $return['percent'][$speed][$key] = empty($this->{$speed}[$type][$key])
            ? 0
            : $return[$key] / $this->{$speed}[$type][$key] * 100;

        return $this;
    }

    /**
     * Update the fastest and slowest values of runtime and used memory of running methods and subjects.
     *
     * @param string $type   method or subject
     * @param float  $time   execution time
     * @param int    $memory memory usage
     *
     * @return $this
     */
    protected function updatePeaks($type, $time, $memory)
    {
        $this->fastest[$type] = [
            'time'   => min($this->fastest[$type]['time'] ?? $time, $time),
            'memory' => min($this->fastest[$type]['memory'] ?? $memory, $memory),
        ];

        $this->slowest[$type] = [
            'time'   => max($this->slowest[$type]['time'] ?? $time, $time),
            'memory' => max($this->slowest[$type]['memory'] ?? $memory, $memory),
        ];

        return $this;
    }

    /**
     * Start test measure.
     */
    protected function start()
    {
        $this->time   = microtime(true);
        $this->memory = memory_get_usage();
    }

    /**
     * Stop test measure.
     */
    protected function stop()
    {
        $this->time   = microtime(true) - $this->time;
        $this->memory = max(0, memory_get_usage() - $this->memory);
    }
}
