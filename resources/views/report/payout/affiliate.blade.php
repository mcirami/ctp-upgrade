@extends('report.template')

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table id="logs" class="affiliate_view table table-striped table-bordered table_01">
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
                            <?php if (!$report->payout_type) : ?>
                                No Details
                                <a class="btn value_span11 value_span2 value_span4 text-decoration-none" href='/user/payment-details'>Submit Details</a>
                            <?php else : ?>
                                {{$report->payout_type}}
                            <?php endif; ?>

                        </div>
                    </div>
                </td>
                <td>{{\Carbon\Carbon::parse($report->start_of_week)->format('m/d/Y')}} - {{\Carbon\Carbon::parse($report->end_of_week)->format('m/d/Y')}}</td>
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
