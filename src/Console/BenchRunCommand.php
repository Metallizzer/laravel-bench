<?php

namespace Metallizzer\Bench\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class BenchRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bench:run {--all : Run all available benchmarks}
                    {--benchmark= : The benchmark that you want to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run benchmark';

    /**
     * The progress bar instance.
     *
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    protected $bar;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        Event::listen('bench.run_before', function ($benchmark) {
            $this->bar = $this->output->createProgressBar(count($benchmark->getMethods()) * count($benchmark->getSubjects()));

            $this->bar->start();
        });

        Event::listen('bench.run_after', function ($benchmark) {
            $this->bar->finish();
            $this->line('');
        });

        Event::listen('bench.subject_completed', function ($subject, $method) {
            $this->bar->advance();
        });
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $benchmarks = [];
        $available  = app('bench')->getBenchmarks();

        if ($this->option('all')) {
            $benchmarks = array_keys($available);
        } else {
            if (null === $benchmark = $this->option('benchmark')) {
                $benchmark = $this->choice('Select benchmark that you want to run', array_keys($available));
            }

            $benchmarks = [$benchmark];

            if (!array_key_exists($benchmark, $available)) {
                $this->error(sprintf('Benchmark "%s" not found', $benchmark));

                return 1;
            }
        }

        $this->line('Running benchmark');

        foreach ($benchmarks as $class) {
            $this->line('');
            $this->info($class);

            $stats = app('bench')->run($class);

            if (!$stats['benchmarks']) {
                continue;
            }

            $this->line('');

            $total      = collect();
            $benchmarks = collect($stats['benchmarks']);
            $maxMethod  = $benchmarks->map(function ($v, $k) {
                return Str::length($k);
            })->max() + 2;
            $maxPercentage = $benchmarks->pluck('percent.fastest.time')->map(function ($v, $k) {
                return Str::length((int) $v);
            })->max() + 2;

            foreach ($benchmarks as $method => $benchmark) {
                $benchmark  = collect($benchmark);
                $subjects   = collect($benchmark['subjects']);
                $percentage = '+'.(int) $benchmark['percent']['fastest']['time'].'%';
                $time       = $subjects->pluck('time');
                $total      = $total->merge($time);

                $this->line(sprintf(
                    '%s%s<info>%s</info> (best [mean] worst) = %s [%s] %s (%s)',
                    str_pad('    '.$method, max($maxMethod, 24), '.'),
                    str_repeat('.', $maxPercentage - Str::length($percentage)),
                    $percentage,
                    $this->convertTime($time->min()),
                    $this->convertTime($time->avg()),
                    $this->convertTime($time->max()),
                    'ms'
                ));
            }

            $this->line('');
            $this->line(sprintf('%s subjects, %s iterations',
                count($stats['subjects']),
                $stats['loops']['base'])
            );
            $this->line(sprintf(
                '(best [mean] worst) = %s [%s] %s (%s)',
                $this->convertTime($total->min()),
                $this->convertTime($total->avg()),
                $this->convertTime($total->max()),
                'ms'
            ));
        }
    }

    protected function convertTime(float $time, $from = 's', $to = 'ms', $precision = 2)
    {
        if (!$time) {
            return 0;
        }

        if ($from === $to) {
            return $time;
        }

        $map = [
            'μs' => 1,
            'ms' => 1000,
            's'  => 1000000,
            'm'  => 60000000,
            'h'  => 3600000000,
            'd'  => 86400000000,
        ];

        return (float) sprintf('%.'.(int) $precision.'f', ($time * $map[$from] ?? 1) / ($map[$to] ?? 1));
    }
}
