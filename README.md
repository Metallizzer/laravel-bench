# Benchmark tool inside your Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/metallizzer/laravel-bench.svg?style=flat-square)](https://packagist.org/packages/metallizzer/laravel-bench)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/metallizzer/laravel-bench.svg?style=flat-square)](https://packagist.org/packages/metallizzer/laravel-bench)

The metallizzer/laravel-bench package provides tool to compare different functions inside your Laravel app.

## Installation

You can install the package via composer:

```bash
composer require metallizzer/laravel-bench
```

Next, you must publish the assets from this package.

```bash
php artisan vendor:publish --provider="Metallizzer\Bench\BenchServiceProvider" --tag="bench-assets"
```

Optionally, you can publish the config file of the package.

```bash
php artisan vendor:publish --provider="Metallizzer\Bench\BenchServiceProvider" --tag="config"
```

This is the content that will be published to `config/bench.php`

```php
return [
    /*
     * The benchmark page will be available on this path.
     */
    'path' => '/bench',

    /*
     * By default this package will only run in local development.
     * Do not change this, unless you know what your are doing.
     */
    'enabled' => env('APP_ENV') === 'local',

    /*
     * The maximum execution time, in seconds. If set to zero, no time limit is imposed.
     */
    'max_execution_time' => 0,
];
```

## Usage

By default this package will only run in a local environment.

Visit `/bench` in your local environment of your app to view the benchmark page.

You can also run benchmarks as artisan command

```bash
php artisan bench:run
```

Use with passing benchmark class name

```bash
php artisan bench:run --benchmark="Benchmark\Class"
```

To run all available benchmarks just issue

```bash
php artisan bench:run --all
```

To create a new command, use the bench:make Artisan command.

```bash
php artisan bench:make
```

This command will create a new benchmark class in the app/Benchmarks directory.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Oleg Petrov](https://github.com/Metallizzer)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
