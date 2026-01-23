@php
    use App\Privilege;
	use LeadMax\TrackYourStats\System\Session;
	$userType = Session::userType();
@endphp
@extends('report.template')

@section('report-title')
    Affiliate Reports
@endsection

@section('table-options')
    @include('report.options.user-type')
    @include('report.options.dates')
    @if ($userType == 0 || $userType == 1)
        <div class="button_wrap" style="width: 100%; display:inline-block; margin-top: 10px;">
            <a style="
			width: 170px;
			border:none;
			padding: 10px;
			font-size: 18px;
			border-radius: 6px;
			color: #676767;"
               class="btn btn-default btn-sm" href="/report/aff-data/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}&role={{$role}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Rep ID</th>
            <th class="value_span9">Rep</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            @if(Session::userType() == Privilege::ROLE_GOD ||
                (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") )
            )
                <th class="value_span9  headers ">Sales Revenue</th>
                <th class="value_span9  ">Deductions</th>
                <th class="value_span9">EPC</th>
                <th class="value_span9">Bonus Revenue</th>
                <th class="value_span9">Referral Revenue</th>
                <th class="value_span9">TOTAL</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
            if (Session::userType() == Privilege::ROLE_GOD ||
            (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") )) {
				$array = [
                    'idrep',
                    'user_name',
                    'Clicks',
                    'UniqueClicks',
                    'FreeSignUps',
                    'PendingConversions',
                    'Conversions',
                    'Revenue',
                    'Deductions',
                    'EPC',
                    'BonusRevenue',
                    'ReferralRevenue',
                    'TOTAL'
                ];
            } else {
				$array = [
                    'idrep',
                    'user_name',
                    'Clicks',
                    'UniqueClicks',
                    'FreeSignUps',
                    'PendingConversions',
                    'Conversions',
                ];
            }
            $reporter->between($dates['startDate'], $dates['endDate'],
            new \LeadMax\TrackYourStats\Report\Formats\HTML(true, $array));
        @endphp
        </tbody>
    </table>
@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function() {
			$("#mainTable").tablesorter(
				{
					sortList: [[7, 1]],
					widgets: ['staticRow']
				});
		});
    </script>
@endsection
