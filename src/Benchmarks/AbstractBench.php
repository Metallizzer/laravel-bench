<?php

namespace Metallizzer\Bench\Benchmarks;

abstract class AbstractBench
{
    public $name        = '';
    public $description = '';
    public $loops       = 1000;
    public $subjects    = [];

    public function runBefore()
    {
    }

    public function runAfter()
    {
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLoops()
    {
        return (int) $this->loops;
    }

    public function getSubjects()
    {
        return $this->subjects ?: [null];
    }
}
