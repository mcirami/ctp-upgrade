@extends('report.template')

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table id="logs" class="table table-striped table-bordered  table_01">
        <thead>
        <tr>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Payout Dates</th>
            <th class="value_span9">Cash Earned</th>
            <th class="value_span9">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>
                    <div class="edit_details">
                        <div class="current_details">
                            {{$report->payout_type ??
                                "No Details" .
                                "<a href='/user/payment-details'>Submit Details</a>"
                               }}
                        </div>
                    </div>
                </td>
                <td>
                    {{$report->start_of_week}} - {{$report->end_of_week}}
                </td>
                <td>
                    ${{ number_format( $report->revenue,2,".",",")}}
                </td>
                <td class="status">
                    {{$report->status}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('footer')

@endsection
