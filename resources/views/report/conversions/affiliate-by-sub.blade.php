@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{ $offerName }} Conversions By Sub Id's
@endsection

@section('table-options')
	{{-- @php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offerId
		);

	@endphp
	@include('report.options.user-clicks-view', $data) --}}
    @include('report.options.dates')
@endsection

@section('table')
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				<th class="value_span9">Sub Id</th>
				<th class="value_span9">Clicks</th>
				<th class="value_span9">Conversions</th>
			</tr>
			</thead>
			<tbody>
			@foreach($report as $row)
				<tr role="row">
					<td>{{$row->sub1}}</td>
					<td>{{$row->clicks}}</td>
					<td>{{$row->conversions}}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	{{ $report->links() }}

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[2, 1]],
					widgets: ['staticRow']
				})
		});

    </script>
@endsection