<?php

namespace Metallizzer\Bench\Benchmarks;

use Illuminate\Support\Str;

class StringBench extends AbstractBench
{
    public $loops = 10000;

    public function runBefore()
    {
        for ($i = 0; $i < 8; ++$i) {
            $this->subjects[] = (rand(0, 1) == 1 ? 'bench' : '').Str::random();
        }
    }

    public function benchRegex($subject)
    {
        return preg_match('/^bench/', $subject) === 1;
    }

    public function benchStrpos($subject)
    {
        return strpos($subject, 'bench') === 0;
    }

    public function benchSubstr($subject)
    {
        return substr($subject, 0, 5) === 'bench';
    }

    public function benchStartsWith($subject)
    {
        return Str::startsWith($subject, 'bench');
    }
}
