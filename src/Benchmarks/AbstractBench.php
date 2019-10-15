<?php

namespace Metallizzer\Bench\Benchmarks;

abstract class AbstractBench
{
    /**
     * The benchmark name.
     *
     * @var string
     */
    public $name = '';

    /**
     * The benchmark description.
     *
     * @var string
     */
    public $description = '';

    /**
     * Number of benchmark loop cycles.
     *
     * @var int
     */
    public $loops = 1000;

    /**
     * Subjects used during benchmarking.
     *
     * @var array
     */
    public $subjects = [];

    /**
     * This method is executed before the benchmark starts.
     *
     * @return mixed
     */
    public function runBefore()
    {
    }

    /**
     * This method is executed after the benchmark ends.
     *
     * @return mixed
     */
    public function runAfter()
    {
    }

    /**
     * Get the name of the benchmark.
     *
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * Get the description of the benchmark.
     *
     * @return string
     */
    final public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the number of benchmark loop cycles.
     *
     * @return int
     */
    final public function getLoops()
    {
        return (int) $this->loops;
    }

    /**
     * Get subjects of the benchmark.
     *
     * @return array
     */
    final public function getSubjects()
    {
        return $this->subjects ?: [null];
    }

    /**
     * Get methods of the benchmark.
     *
     * @return array
     */
    final public function getMethods()
    {
        return array_filter(get_class_methods($this), function ($method) {
            return strpos($method, 'bench') === 0;
        });
    }
}
