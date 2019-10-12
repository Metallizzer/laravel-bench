<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="robots" content="noindex, nofollow">

    <title>bench{{ config('app.name') ? ' - ' . config('app.name') : '' }}</title>

    <!-- Style sheets-->
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset(mix('app.css', 'vendor/bench')) }}" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">Laravel Bench</div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger alert-important" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-sm-flex align-items-center justify-content-between @hasSection('content') mb-4 @endif">
                                    <form class="form-inline" action="{{ route('bench.run') }}" method="post">
                                        @csrf
                                        <label class="my-1 mr-2" for="benchmark">Benchmark</label>
                                        <select class="custom-select my-1 mr-sm-2" id="benchmark" name="benchmark">
                                            <option value="">Choose benchmark</option>
                                            @foreach (app('bench')->getBenchmarks() as $key => $bench)
                                                <option value="{{ $key }}"@if ($selected === $key) selected="selected"@endif>{{ $bench['name'] }}</option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-primary my-1">Run</button>
                                    </form>
                                    
                                    @yield('button')
                                </div>

                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset(mix('app.js', 'vendor/bench')) }}" type="text/javascript"></script>
    @stack('scripts')
</body>
</html>
