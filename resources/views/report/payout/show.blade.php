@extends('report.template')

@section('report-title')
    Payout Logs
@endsection
<div id="error_message">
    <p></p>
</div>
@section('table')
    <table id="logs" class="table table-striped table-bordered table_01 tablesorter">
        <thead>
        <tr role="row" class="tablesorter-headerRow">
            <th class="value_span9">Username</th>
            <th class="value_span9">Payout Dates</th>
            <th class="value_span9">Cash Earned</th>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Payout ID</th>
            <th class="value_span9">Payout Country</th>
            <th class="value_span9">Payout Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>
                    <span>
                        {{$report->user_name}}
                    </span>
                </td>
                <td>{{$report->start_of_week}} - {{$report->end_of_week}}</td>
                <td>${{ number_format( $report->revenue,2,".",",")}}
                </td>
                <td>
                    <div class="edit_details">
                        <div class="current_details">
                            {{$report->payout_type ?? "No Details"}}
                            {{--<a class="edit_payout_details" href="#">edit</a>--}}
                        </div>
                        {{--<div class="input_field">
                            <select>
                                <option value=null>{{$report->payout_type ?? "No Details"}}</option>
                                <option value="wise">Wise</option>
                                <option value="paypal">Paypal</option>
                            </select>
                            <input data-log="{{$report->log_id}}" type="text" name="payout_type" value="{{$report->payout_type ?? "No Details"}}" />
                            <a class="cancel_payout_details" href="#">cancel</a>
                        </div>--}}
                    </div>
                </td>
                <td>
                    {{$report->payout_id ?? "No Details"}}
                </td>
                <td >
                    {{$report->country ?? "No Details"}}
                </td>
                <td class="status">
                    @if ($report->status == "rollover")
                        {{$report->status}}
                    @elseif($report->status == "pending")
                        <span class="status_wrap">
                            {{$report->status}}
                            <span class="btn_span">
                                <a data-status="{{$report->status}}" data-log="{{$report->log_id}}" class="payout_status_button btn value_span11 value_span2 value_span4" href="#">
                                    MARK PAID
                                </a>
                            </span>
                        </span>
                    @else
                        {{$report->status}}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('extra')
    {{ $reports->links() }}
@endsection

@section('footer')
    <script type="text/javascript">

		$(document).ready(function () {
			$("#logs")
				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[1, 1]],
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
		});

    </script>
@endsection