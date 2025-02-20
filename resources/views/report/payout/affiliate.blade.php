@extends('report.template')

@section('table-options')
    @include('report.options.dates')
@endsection

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table class="table table-striped table-bordered  table_01">
        <thead>
        <tr>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Notes</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Date Achieved</th>
        </tr>
        </thead>
        <tbody>
        @isset($report)
            @php
                $report->printReports();
            @endphp
        @endif
        </tbody>
    </table>
@endsection

@section('footer')
    <script type="text/javascript" defer>
        new Vue({
            'el': '#apptwo',
            data: {
                reportData: [],
                activeIds: []
            },
            methods: {
                fetchHistoryReport(startDate, endDate, rowId) {
                    let payout = axios.get('/report/payout?d_from=' + startDate + '&d_to=' + endDate + '&adminLogin');
                    let offer = axios.get('/report/offer?d_from=' + startDate + '&d_to=' + endDate + '&adminLogin');

                    axios.all([payout, offer])
                        .then(
                            axios.spread((payoutData, offerData) => {
                                this.reportData[rowId] = payoutData.data;
                                this.reportData[rowId].offerReport = offerData.data;
                                this.activeIds.push(rowId);
                            })
                        ).catch(err => console.log(err));

                },

                deActiveReport(id) {
                    this.activeIds.splice(this.activeIds.indexOf(id), 1);
                }


            }
        });
    </script>
@endsection
