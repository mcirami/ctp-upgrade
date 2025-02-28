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
                <td>{{\Carbon\Carbon::parse($report->start_of_week)->format('m/d/Y')}} - {{\Carbon\Carbon::parse($report->end_of_week)->format('m/d/Y')}}</td>
                <td>${{ number_format( $report->revenue,2,".",",")}}
                </td>
                <td>
                    <div class="edit_details">
                        <div class="current_details">
                            <p class="current_text">
                                {{$report->payout_type ?? "No Details"}}
                            </p>
                            <a class="edit_payout_detail" href="#">edit</a>
                        </div>
                        <div class="input_field">
                            <select class="payout_detail" name="payout_type" id="payout_type" data-log="{{$report->log_id}}">
                                <option value="wise" <?php if($report->payout_type == "wise") echo "selected"; ?>>Wise</option>
                                <option value="paypal" <?php if($report->payout_type == "paypal") echo "selected"; ?>>Paypal</option>
                            </select>
                            <a class="cancel_payout_detail" href="#">cancel</a>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="edit_details">
                        <div class="current_details">
                            <p class="current_text">
                                {{$report->payout_id ?? "No Details"}}
                            </p>
                            <a class="edit_payout_detail" href="#">edit</a>
                        </div>
                        <div class="input_field">
                            <input name="payout_id" class="payout_detail" type="text" data-log="{{$report->log_id}}" value=" {{$report->payout_id ?? ""}}">
                            <a class="cancel_payout_detail" href="#">cancel</a>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="edit_details">
                        <div class="current_details">
                            <p class="current_text">
                                {{$report->country ?? "No Details"}}
                            </p>
                            <a class="edit_payout_detail" href="#">edit</a>
                        </div>
                        <div class="input_field">
                            <select class="payout_detail" class="selectBox" name="country" id="payout_country" data-log="{{$report->log_id}}">
                                <option value="">Select Your Country</option>
                                @foreach (config('countries') as $key => $country)
                                    <option value="{{$key}}" <?php if ($key == $report->country) echo "selected"; ?>>{{$country['name']}}</option>
                                @endforeach
                            </select>
                            <a class="cancel_payout_detail" href="#">cancel</a>
                        </div>
                    </div>

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