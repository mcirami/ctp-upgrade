@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
        <thead>
        <tr>
            @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                <th class="value_span9">Click ID</th>
            @endif
            <th class="value_span9">Timestamp</th>
            <th class="value_span9">Conversion Timestamp</th>
            <th class="value_span9">Paid</th>
            <th class="value_span9">Sub 1</th>
            <th class="value_span9">Sub 2</th>
            <th class="value_span9">Sub 3</th>
            <th class="value_span9">Sub 4</th>
            <th class="value_span9">Sub 5</th>

            @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                <th class="value_span9">IP Address</th>
            @endif


            @php
                $report->printHeaders();
            @endphp


            <th class="value_span9">Iso Code</th>
            @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                <th class="value_span9">Sub Division</th>
                <th class="value_span9">City</th>
                <th class="value_span9">Postal</th>
                <th class="value_span9">Longitude</th>
                <th class="value_span9">Latitude</th>
            @endif
        </tr>
        </thead>
        <tbody>



        @php
            $report->process();
            $report->printR();
        @endphp

        </tbody>
    </table>
    @include('report.options.pagination')

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {
			$("#clicks").tablesorter(
				{
					sortList: [[3, 1]],
					widgets: ['staticRow']
				});
		});

    </script>
@endsection