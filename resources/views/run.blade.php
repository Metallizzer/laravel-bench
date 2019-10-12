@extends('bench::index')

@section('button')
    <a id="toggle_all" href="#" class="btn btn-primary my-1">Toggle all</a>
@endsection

@section('content')
    <div class="accordion" id="accordionBench">
        @foreach ($stats['benchmarks'] as $method => $benchmark)
            <div class="card">
                <div class="card-header position-relative collapsed" id="heading{{ $loop->iteration }}" data-toggle="collapse" data-target="#collapse{{ $loop->iteration }}" aria-expanded="false" aria-controls="collapse{{ $loop->iteration }}">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $benchmark['percent']['slowest']['time'] }}%; background-color: hsl({{ (1-$benchmark['grade'])*120 }}, 100%, 50%);" aria-valuenow="{{ $benchmark['percent']['slowest']['time'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="float-left badge badge-dark position-relative">{{ $method }}</div>
                    <div class="float-right badge badge-dark position-relative">+{{ (int) $benchmark['percent']['fastest']['time'] }}%</div>
                </div>

                <div id="collapse{{ $loop->iteration }}" class="collapse" aria-labelledby="heading{{ $loop->iteration }}" data-parents="#accordionBench">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="w-50">subject → return</th>
                                <th class="text-right">{{ sprintf('%.6f', $benchmark['memory'] / pow(1024, 2)) }}MB</th>
                                <th class="text-right">{{ sprintf('%01.6f', $benchmark['time']) }}s</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($benchmark['subjects'] as $key => $subject)
                                <tr>
                                    <td>
                                        <strong title="({{ gettype($stats['subjects'][$key]) }}) {{ var_export($stats['subjects'][$key], true) }}">[{{ $key }}] →</strong>
                                        <span class="text-muted">({{ gettype($subject['return']) }})</span>
                                        {{ var_export($subject['return'], true) }}
                                    </td>
                                    <td class="text-right">
                                        {{ sprintf('%.6f', $subject['memory'] / pow(1024, 2)) }}MB
                                    </td>
                                    <td class="text-right">
                                        {{ sprintf('%01.6f', $subject['time']) }}s
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#toggle_all').on('click', function(e) {
                e.preventDefault();

                if ($('#accordionBench .collapsed').length) {
                    $('#accordionBench .collapse').collapse('show');
                }  else {
                    $('#accordionBench .collapse').collapse('hide');
                }
            })
        });
    </script>
@endpush