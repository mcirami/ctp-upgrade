<table>
    <thead>
    <tr>
        <th>Click ID</th>
        <th>Timestamp</th>
        <th>Conversion Timestamp</th>
        <th>Paid</th>
        <th>Sub 1</th>
        <th>Sub 2</th>
        <th>Sub 3</th>
        <th>Affiliate</th>
        <th>Offer ID</th>
        <th>Referer</th>
        <th>IP</th>
    </tr>
    </thead>
    <tbody>
    @php $myReport = new LeadMax\TrackYourStats\Table\Date; @endphp
    @foreach ($countryClicks as $data)
        @php
            $timestamp = $myReport->convertToEST($data['first_timestamp']);
            $conversionTimeStamp = "";
            if ($data['conversion_timestamp']) {
                $conversionTimeStamp = $myReport->convertToEST($data['conversion_timestamp']);
            }
        @endphp
        <tr>
            <td>{{ $data['idclicks'] }}</td>
            <td>{{ $timestamp }}</td>
            <td>{{ $conversionTimeStamp }}</td>
            <td>{{ $data['paid'] }}</td>
            <td>{{ $data['sub1'] }}</td>
            <td>{{ $data['sub2'] }}</td>
            <td>{{ $data['sub3'] }}</td>
            <td>{{ $data['rep_idrep'] }}</td>
            <td>{{ $data['offer_idoffer'] }}</td>
            <td>{{ $data['referer'] }}</td>
            <td>{{ $data['click_geo_ip'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
