@extends('report.template')

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table id="logs" class="affiliate_view table table-striped table-bordered table_01">
        <thead>
        <tr>
            <th class="value_span9">Payout Dates</th>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Cash Earned</th>
            <th class="value_span9">Status</th>
        </tr>
        </thead>
    </table>
@endsection

@section('footer')
    @push('scripts')
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
        <script type="text/javascript">

			$(document).ready(function () {
				const table = $('#logs').DataTable({
					ajax: {
						url: '/report/payout/get-aff-logs',
						dataSrc: 'data',
					},
					processing: true,
					serverSide: true,
					pageLength: 50,
					lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
					columns: [
						{
							data: 'payout_dates',
							name: 'payout_logs.start_of_week',
							orderable: true,
							searchable: false
						},
						{data: 'payout_type', name: 'payout_logs.payout_type'},
						{data: 'revenue', name: 'payout_logs.revenue'},
						{data: 'status', name: 'payout_logs.status'},
					],
					initComplete: function (settings, json) {
						// This callback runs every time the table is redrawn (including paging)
						$('.dt-paging .dt-paging-button.current').addClass('button active value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4');
						$('.dt-paging .dt-paging-button:not(.current)').addClass('button value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4')
						// You could also conditionally add different classes for first/last/etc
					},
					drawCallback: function (settings) {
						// This callback runs every time the table is redrawn (including paging)
						$('.dt-paging .dt-paging-button.current').addClass('button active value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4');
						$('.dt-paging .dt-paging-button:not(.current)').addClass('button value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4')
						// You could also conditionally add different classes for first/last/etc
					},
					stateLoadCallback: function (settings, callback) {
						console.log('state callback');
					}
				});

				$(window).on('resize', function() {
					// forcibly re-draw or re-apply classes
					// table.draw(false) will re-draw while maintaining paging
					table.draw(false);
				});

			});

        </script>
    @endpush
@endsection
