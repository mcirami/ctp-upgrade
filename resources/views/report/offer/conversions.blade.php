@extends('report.template')

@section('report-title')
    Click Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')

@endsection


@section('footer')
    <script type="text/javascript">

		$(document).ready(function () {
			$('#mainTable').tablesorter(
				{
					sortList: [[6, 1]],
					widgets: ['staticRow'],
				});
		});
    </script>
@endsection