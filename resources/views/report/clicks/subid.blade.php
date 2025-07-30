@php 
	use LeadMax\TrackYourStats\System\Session;
	use App\Privilege;
	$canViewFraudData = Session::permissions()->can("view_fraud_data");
@endphp

@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Sub Id '{{$subId}}' Clicks For Offer '{{$offer->offer_name}}'
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
	<div class="form-group searchDiv">
		@if ($canViewFraudData)
			<form action="/user/{{$user->idrep}}/search-clicks" method="GET">
				<input id="searchBox"
					   class="form-control"
					   type="text"
					   name="searchValue"
					   placeholder="Search Click ID"
				/>
				<input type="hidden" name="d_from" value="{{$startDate}}">
				<input type="hidden" name="d_to" value="{{$endDate}}">
				<input type="hidden" name="dateSelect" value="{{$dateSelect}}">
				<input type="hidden" name="searchType" value="user">
			</form>
		@endif
	</div>
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				@if ($canViewFraudData)
					<th class="value_span9">Click ID</th>
				@endif
				<th class="value_span9">Timestamp</th>
				<th class="value_span9">Conversion Timestamp</th>
				@if ($canViewFraudData || (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") ))
					<th class="value_span9">Paid</th>
				@endif
				<th class="value_span9">Referer Url</th>
				@if ($canViewFraudData)
					<th class="value_span9">IP Address</th>
				@endif
				<th class="value_span9">Iso Code</th>
				@if ($canViewFraudData)
					<th class="value_span9">Sub Division</th>
					<th class="value_span9">City</th>
					<th class="value_span9">Postal</th>
					<th class="value_span9">Longitude</th>
					<th class="value_span9">Latitude</th>
				@endif
			</tr>
			</thead>
			<tbody>
			@php $myReport = new LeadMax\TrackYourStats\Table\Date;  @endphp
			@foreach($report as $row)
				@php 
					$timestamp = $myReport->convertToEST($row->timestamp);
					$convertionTimeStamp = "";
					if ($row->conversion_timestamp) {
						$convertionTimeStamp = $myReport->convertToEST($row->conversion_timestamp);
					}
					
				@endphp
				<tr role="row">
					@if ($canViewFraudData)
						<td>{{$row->idclicks}}</td>
					@endif
					<td>{{$timestamp}}</td>
					<td>{{$convertionTimeStamp}}</td>
					@if ($canViewFraudData || (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") ))
						<td>{{$row->paid}}</td>
					@endif
					<td>{{$row->referer}}</td>
					@if ($canViewFraudData)
						<td>{{$row->ip_address}}</td>
					@endif
					<td>{{$row->isoCode}}</td>
					@if ($canViewFraudData)
						<td>{{$row->subDivision}}</td>
						<td>{{$row->city}}</td>
						<td>{{$row->postal}}</td>
						<td>{{$row->latitude}}</td>
						<td>{{$row->longitude}}</td>
					@endif

				</tr>


			@endforeach
			</tbody>
		</table>
	</div>
	{{ $reportCollection->links() }}

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[4, 1]],
					widgets: ['staticRow']
				})

				// bind to pager events
				// *********************
				/*.bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c) {
					var msg = '"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
						' page <span class="typ">' + (c.page + 1) + '/' + c.totalPages + '</span>';
					$('#display')
					.append('<li><span class="str">"' + e.type + msg + '</li>')
					.find('li:first').remove();
				})*/

				// initialize the pager plugin
				// ****************************
				//.tablesorterPager(pagerOptions);
		});

    </script>
@endsection