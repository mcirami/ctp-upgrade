@php use App\Privilege;use LeadMax\TrackYourStats\System\Session; @endphp
@extends('report.template')

@section('report-title')
    Offer Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table_01 tablesorter" id="mainTable">
        <thead>

        <tr>
            <th class="value_span9">Offer ID</th>
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversion</th>
            <th class="value_span9">Conversion</th>
            @if (Session::userType() == Privilege::ROLE_GOD ||
                (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") ))
                <th class="value_span9">Revenue</th>
                <th class="value_span9">Deductions</th>
                <th class="value_span9">EPC</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
            if (Session::userType() == Privilege::ROLE_GOD ||
                (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") )
            ) {
				$array = ['idoffer', 'offer_name', 'Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'EPC'];
            } else {
                $array = ['idoffer', 'offer_name', 'Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions'];
            }

            $reporter->between($dates['startDate'], $dates['endDate'],
             new LeadMax\TrackYourStats\Report\Formats\HTML(true,
             $array, $dates));
        @endphp

        </tbody>
    </table>
@endsection
@section('footer')
    <script type="text/javascript">
		$(document).ready(function() {
			$("#mainTable").tablesorter(
				{
					sortList: [[6, 1]],
					widgets: ['staticRow']
				});
		});
    </script>
@endsection