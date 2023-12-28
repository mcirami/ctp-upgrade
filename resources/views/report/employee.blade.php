@extends('report.template')

@section('report-title')
    Affiliate Reports
@endsection

@section('table-options')
    @include('report.options.user-type')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Rep ID</th>
            <th class="value_span9">Rep</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            <th class="value_span9  headers ">Sales Revenue</th>
            <th class="value_span9  ">Deductions</th>
            <th class="value_span9">EPC</th>
            <th class="value_span9">Bonus Revenue</th>
            <th class="value_span9">Referral Revenue</th>
            <th class="value_span9">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'],
            new \LeadMax\TrackYourStats\Report\Formats\HTML(true, [
                'idrep',
                'user_name',
                'Clicks',
                'UniqueClicks',
                'FreeSignUps',
                'PendingConversions',
                'Conversions',
                'Revenue',
                'Deductions',
                'EPC',
                'BonusRevenue',
                'ReferralRevenue',
                'TOTAL'
            ]));
        @endphp
        </tbody>
    </table>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[7, 1]],
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection
