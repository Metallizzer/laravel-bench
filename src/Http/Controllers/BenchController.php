<?php

namespace Metallizzer\Bench\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class BenchController extends Controller
{
    public function index()
    {
        return view('bench::index', [
            'selected' => false,
        ]);
    }

    public function run(Request $request)
    {
        $bench      = app('bench');
        $benchmarks = $bench->getBenchmarks();
        $data       = $request->validate([
            'benchmark' => ['required', Rule::in(array_keys($benchmarks))],
        ]);

        return view('bench::run', [
            'stats'    => $bench->run($data['benchmark']),
            'selected' => $data['benchmark'],
        ]);
    }
}
