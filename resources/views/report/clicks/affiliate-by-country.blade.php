@php
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks By Country
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <div class="table_wrap">
        <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
            <thead>
            <tr>
                <th class="value_span9">Country</th>
                <th class="value_span9">Raw Clicks</th>
                <th class="value_span9">Unique Clicks</th>
                <th class="value_span9">Conversions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($reports as $key => $row)
                <tr role="row">
                    <td>{{$key ?: 'Unknown'}}</td>
                    <td>{{$row['total_clicks']}}</td>
                    <td>{{$row['unique_clicks']}}</td>
                    <td>{{$row['total_conversions']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#clicks").tablesorter({
                sortList: [[1, 1]],
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
