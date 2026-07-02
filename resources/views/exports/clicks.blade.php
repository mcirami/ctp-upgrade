<table>
    <thead>
        <tr>
            <th>Click ID</th>
            <th>Timestamp</th>
            <th>Offer Name</th>
            <th>Conversion Timestamp</th>
            <th>Paid</th>
            <th>Sub1</th>
            <th>Sub2</th>
            <th>Sub3</th>
            <th>Referer URL</th>
            <th>IP Address</th>
            <th>Iso Code</th>
            <th>Sub Division</th>
            <th>City</th>
            <th>Postal</th>
            <th>Longitude</th>
            <th>Latitude</th>
        </tr>
    </thead>
    <tbody>
        @php $myReport = new LeadMax\TrackYourStats\Table\Date; @endphp
        @foreach ($clicks as $click)
        @php
            $timestamp = $myReport->convertToEST($click->timestamp);
            $conversionTimeStamp = "";
            if ($click->conversion_timestamp) {
                $conversionTimeStamp = $myReport->convertToEST($click->conversion_timestamp);
            }
        @endphp
        <tr>
            <td>{{ $click->idclicks }}</td>
            <td>{{ $timestamp }}</td>
            <td>{{ $click->offer_name }}</td>
            <td>{{ $conversionTimeStamp }}</td>
            <td>{{ $click->paid }}</td>
            <td>{{ $click->sub1 }}</td>
            <td>{{ $click->sub2 }}</td>
            <td>{{ $click->sub3 }}</td>
            <td>{{ $click->referer }}</td>
            <td>{{ $click->ip_address }}</td>
            <td>{{ $click->iso_code }}</td>
            <td>{{ $click->subDivision}}</td>
            <td>{{ $click->city }}</td>
            <td>{{ $click->postal }}</td>
            <td>{{ $click->latitude}}</td>
            <td>{{ $click->longitude}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
