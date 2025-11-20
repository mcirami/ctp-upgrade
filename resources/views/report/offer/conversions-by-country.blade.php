@extends('report.template')

@section('report-title')
    {{$offer?->offer_name}} Conversions By Country
@endsection

@section('table-options')
	@php
		$data = array(
			'd_from' 		=> request()->query('d_from'),
			'd_to'			=> request()->query('d_to'),
			'dateSelect'	=> request()->query('dateSelect'),
			'offerId' 		=> $offer ? $offer->idoffer : null
		);
		if ($offer) :
	@endphp
		@include('report.options.offer_conversions_view', $data)
	@php endif @endphp

	@include('report.options.dates')
@endsection

@section('table')
	<table class="table table-bordered table_01 tablesorter" id="mainTable">
		<thead>

		<tr>
			<th class="value_span9">Country</th>
			<th class="value_span9">Clicks</th>
			<th class="value_span9">Unique Clicks</th>
			<th class="value_span9">Conversions</th>
		</tr>
		</thead>
		<tbody>
        @php
            $rows = $affiliateReport['rows'] ?? [];
            $totals = $affiliateReport['totals'] ?? null;
        @endphp

        @forelse($rows as $report)
            <tr>
                <td>{{$report['country_code'] === 'ZZ' ? 'Unknown' : $report['country_code']}}</td>
                <td>{{$report['total_clicks']}}</td>
                <td>{{$report['unique_clicks']}}</td>
                <td>{{$report['total_conversions']}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No data available for the selected range.</td>
            </tr>
        @endforelse

		</tbody>
        @if(!empty($rows) && $totals)
            <tfoot>
            <tr class="static">
                <td><strong>Total</strong></td>
                <td><strong>{{$totals['total_clicks']}}</strong></td>
                <td><strong>{{$totals['unique_clicks']}}</strong></td>
                <td><strong>{{$totals['total_conversions']}}</strong></td>
            </tr>
            </tfoot>
        @endif
	</table>
@endsection


@section('footer')
    <script type="text/javascript">

		$(document).ready(function () {
			$('#mainTable').tablesorter(
				{
					sortList: [[3, 1]],
					widgets: ['staticRow'],
				});
		});
    </script>
@endsection