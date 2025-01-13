@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Conversions By Country
@endsection

@section('table-options')

    @php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offerId
		);

	@endphp
	@include('report.options.user-clicks-view', $data)
    @include('report.options.dates')
@endsection

@section('table')
<div class="table_wrap">
    <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Country</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Unique Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $key => $row)
            <tr role="row">
                <td>{{$key}}</td>
                <td>{{$row['total_clicks']}}</td>
                <td>{{$row['unique_clicks']}}</td>
                <td>{{$row['total_conversions']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection