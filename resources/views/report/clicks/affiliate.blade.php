@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
	<div class="form-group searchDiv">
		@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
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

				{{--<div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
					<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none"
						 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round"
							  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
					</svg>
				</div>--}}
			</form>
		@endif
	</div>
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
					<th class="value_span9">Click ID</th>
				@endif
				<th class="value_span9">Timestamp</th>
				<th class="value_span9">Offer Name</th>
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

			@foreach($report as $row)
				<tr role="row">
					@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
						<td>{{$row->idclicks}}</td>
					@endif
					<td>{{$row->first_timestamp}}</td>
					<td>{{$row->offer_name}}</td>
					<td>{{$row->timestamp}}</td>
					<td>{{$row->paid}}</td>
					<td>{{$row->sub1}}</td>
					<td>{{$row->sub2}}</td>
					<td>{{$row->sub3}}</td>
					<td>{{$row->sub4}}</td>
					<td>{{$row->sub5}}</td>
					@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
						<td>{{$row->ip}}</td>
					@endif
					<td>{{$row->isoCode}}</td>
					@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
						<td>{{$row->isoCode}}</td>
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
	{{ $reportData->links() }}

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
				.bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c) {
					var msg = '"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
						' page <span class="typ">' + (c.page + 1) + '/' + c.totalPages + '</span>';
					$('#display')
					.append('<li><span class="str">"' + e.type + msg + '</li>')
					.find('li:first').remove();
				})

				// initialize the pager plugin
				// ****************************
				//.tablesorterPager(pagerOptions);
		});

    </script>
@endsection