<?php

namespace Metallizzer\Bench;

use Metallizzer\Bench\Benchmarks\AbstractBench;
use ReflectionMethod;

class Runner
{
    protected $methods = [];
    protected $benchmark;
    protected $time;
    protected $memory;
    protected $method;
    protected $benchmarks = [];
    protected $fastest    = [];
    protected $slowest    = [];

    public function __construct(AbstractBench $benchmark)
    {
        $this->benchmark = $benchmark;
        $this->methods   = array_filter(get_class_methods($this->benchmark), function ($method) {
            return strpos($method, 'bench') === 0;
        });
    }

    public function run()
    {
        $this->benchmarks = $this->fastest = $this->slowest = [];

        $this->benchmark->runBefore();
        $this->runBenchmarks();
        $this->calculatePercentage();
        $this->benchmark->runAfter();
    }

    public function getStats()
    {
        return [
            'class'       => get_class($this->benchmark),
            'name'        => $this->benchmark->getName(),
            'description' => $this->benchmark->getDescription(),
            'loops'       => [
                'base'  => $this->benchmark->getLoops(),
                'total' => $this->benchmark->getLoops() * count($this->benchmark->getSubjects()) * count($this->methods),
            ],
            'subjects'   => $this->benchmark->getSubjects(),
            'benchmarks' => $this->benchmarks,
        ];
    }

    protected function runBenchmarks()
    {
        foreach ($this->methods as $method) {
            $this->benchmarks[$method] = [
                'time'     => 0,
                'memory'   => 0,
                'subjects' => [],
            ];

            $reflection = new ReflectionMethod(get_class($this->benchmark), $method);

            foreach ($this->benchmark->getSubjects() as $key => $subject) {
                $return = $reflection->invoke($this->benchmark, $subject);

                unset($result);

                $this->start();

                for ($i = 0; $i < $this->benchmark->getLoops(); ++$i) {
                    $result = $reflection->invoke($this->benchmark, $subject);
                }

                $this->end();

                $this->benchmarks[$method]['subjects'][$key] = [
                    'return' => $return,
                    'time'   => $this->time,
                    'memory' => $this->memory,
                ];

                $this->benchmarks[$method]['time']   += $this->time;
                $this->benchmarks[$method]['memory'] += $this->memory;

                $this->updatePeaks('subject', $this->time, $this->memory);
            }

            $this->updatePeaks('method', $this->benchmarks[$method]['time'], $this->benchmarks[$method]['memory']);
        }
    }

    protected function calculatePercentage()
    {
        foreach ($this->benchmarks as &$method) {
            $this->setPercentage('fastest', 'method', 'time', $method);
            $this->setPercentage('fastest', 'method', 'memory', $method);
            $this->setPercentage('slowest', 'method', 'time', $method);
            $this->setPercentage('slowest', 'method', 'memory', $method);

            $method['grade'] = ($method['time'] - $this->fastest['method']['time']) / ($this->slowest['method']['time'] - $this->fastest['method']['time']);

            foreach ($method['subjects'] as &$subject) {
                $this->setPercentage('fastest', 'subject', 'time', $subject);
                $this->setPercentage('fastest', 'subject', 'memory', $subject);
                $this->setPercentage('slowest', 'subject', 'time', $subject);
                $this->setPercentage('slowest', 'subject', 'memory', $subject);

                $subject['grade'] = ($subject['time'] - $this->fastest['subject']['time']) / ($this->slowest['subject']['time'] - $this->fastest['subject']['time']);
            }
        }
    }

    protected function setPercentage($speed, $type, $key, &$return)
    {
        $return['percent'][$speed][$key] = empty($this->{$speed}[$type][$key])
            ? 0
            : $return[$key] / $this->{$speed}[$type][$key] * 100;
    }

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
    }

    protected function start()
    {
        $this->time   = microtime(true);
        $this->memory = memory_get_usage();
    }

    protected function end()
    {
        $this->time   = microtime(true) - $this->time;
        $this->memory = max(0, memory_get_usage() - $this->memory);
    }
}
