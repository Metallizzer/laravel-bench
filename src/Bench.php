<?php

namespace Metallizzer\Bench;

use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Metallizzer\Bench\Benchmarks\AbstractBench;
use ReflectionClass;

class Bench
{
    protected $app;
    protected $benchmarks;
    protected $paths;

    public function __construct(Application $app)
    {
        set_time_limit(config('bench.max_execution_time'));

        $this->app = $app;
        $namespace = $this->app->getNamespace();

        $this->setPaths([
            'Metallizzer\\Bench\\Benchmarks\\' => __DIR__.DIRECTORY_SEPARATOR.'Benchmarks',
            $namespace.'Benchmarks\\'          => app_path('Benchmarks'),
        ]);

        $this->getBenchmarks();
    }

    public function setPaths($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $this->benchmarks = null;
        $this->paths      = array_filter($paths, function ($path) {
            return is_dir($path);
        });
    }

    public function getBenchmarks()
    {
        if (null !== $this->benchmarks) {
            return $this->benchmarks;
        }

        $this->benchmarks = [];

        foreach ($this->paths as $namespace => $path) {
            foreach (glob($path.'/*.php') as $file) {
                $class = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($file, $path.DIRECTORY_SEPARATOR)
                );

                if (is_subclass_of($class, AbstractBench::class)
                    && !($reflection = new ReflectionClass($class))->isAbstract()
                ) {
                    $properties = $reflection->getDefaultProperties();

                    $this->benchmarks[$reflection->getName()] = [
                        'name'        => $properties['name'] ?: $reflection->getName(),
                        'description' => $properties['description'],
                    ];
                }
            }
        }

        return $this->benchmarks;
    }

    public function run($benchmark)
    {
        $benchmarks = $this->getBenchmarks();

        if (!array_key_exists($benchmark, $benchmarks)) {
            throw new InvalidArgumentException(sprintf('Benchmark "%s" not found', $benchmark));
        }

        $runner = new Runner(new $benchmark());

        $runner->run();

        return $runner->getStats();
    }
}
