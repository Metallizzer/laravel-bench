<?php

namespace Metallizzer\Bench\Benchmarks;

use Illuminate\Support\Str;

class HashBench extends AbstractBench
{
    public function runBefore()
    {
        for ($i = 0; $i < 32; ++$i) {
            $this->subjects[] = Str::random();
        }
    }

    public function benchMD5($subject)
    {
        return hash('md5', $subject);
    }

    public function benchSHA1($subject)
    {
        return hash('sha1', $subject);
    }

    public function benchSHA256($subject)
    {
        return hash('sha256', $subject);
    }

    public function benchSHA512($subject)
    {
        return hash('sha512', $subject);
    }
}
