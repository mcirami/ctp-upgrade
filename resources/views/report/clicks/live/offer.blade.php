@extends('report.template')

@section('report-title')
    {{$offer->offer_name}}'s Clicks
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table id="reps" class="table table-striped table-bordered table-condensed table_01 tablesorter ">
        <thead>
        <tr>
            <th class="value_span9">Affiliate ID</th>
            <th class="value_span9">First Name</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($affiliateReport as $row)
            <tr>
                <td>{{$row->user_id}}</td>
                <td>{{$row->first_name}}</td>
                <td>{{$row->clicks}}</td>
                <td>{{$row->conversions}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    </div>
    <div class="white_box_x_scroll white_box manage_aff large_table value_span8  one_hungee_table"
         style="width:100%;!important;">
        <table id="clicks" class="table table-striped table-bordered table_01 tablesorter">
            <thead>
            <tr>
                @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                    <th class="value_span9">Click ID</th>
                @endif
                <th class="value_span9"><br>Timestamp</th>
                <th class="value_span9">Conversion Timestamp</th>
                <th class="value_span9">Paid</th>
                <th class="value_span9">Sub 1</th>
                <th class="value_span9">Sub 2</th>
                <th class="value_span9">Sub 3</th>
                <th class="value_span9">Sub 4</th>
                <th class="value_span9">Sub 5</th>
                <th class="value_span9">Affiliate</th>
                <th class="value_span9">Offer</th>
                @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                    <th class="value_span9">Ip Address</th>
                    <th class=\"value_span9\">Sub Division</th>
                    <th class=\"value_span9\">City</th>
                    <th class=\"value_span9\">Postal</th>
                    <th class=\"value_span9\">Longitude</th>
                    <th class=\"value_span9\">Latitude</th>
                @endif
                <th class="value_span9">Iso Code</th>
            </tr>
            </thead>
            <tbody>
            @foreach($clickReport as $row)
                <tr>
                    @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                        <td>{{$row['id']}}</td>
                    @endif
                    <td>{{$row['timestamp']}}</td>
                    <td>{{$row['conversion_timestamp']}}</td>
                    <td>{{$row['paid']}}</td>
                    @for($i = 1; $i <= 5; $i++)
                        <td>{{$row['sub' . $i]}}</td>
                    @endfor
                    <td>{{$row['affiliate_id']}}</td>
                    <td>{{$row['offer_id']}}</td>
                    @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
                        <td>{{isset($row['ip_address']) ? $row['ip_address'] : ""}}</td>
                        <td>{{isset($row['subDivision']) ? $row['subDivision'] : ""}}</td>
                        <td>{{isset($row['city']) ? $row['city'] : ""}}</td>
                        <td>{{isset($row['postal']) ? $row['postal'] : ""}}</td>
                        <td>{{isset($row['latitude']) ? $row['latitude'] : ""}}</td>
                        <td>{{isset($row['longitude']) ? $row['longitude'] : ""}}</td>
                    @endif
                    <td>{{isset($row['isoCode']) ? $row['isoCode'] : ""}}</td>
                </tr>
            @endforeach
            <tr>
            </tr>
            </tbody>
        </table>
@endsection

@section('extra')
    {{$clickReport->links()}}
    @include('report.options.rows_per_page')
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#reps").tablesorter(
                {
                    sortList: [[3, 1]],
                    widgets: ['staticRow']
                });
        });

        $(document).ready(function () {
	        $("#clicks").tablesorter(
		        {
			        sortList: [[3, 1]],
			        widgets: ['staticRow']
		        });
        });
    </script>
@endsection