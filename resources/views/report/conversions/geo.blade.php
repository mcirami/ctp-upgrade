@php
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    Conversions By Country
@endsection

@section('table-options')

    @if(Session::userType() != Privilege::ROLE_AFFILIATE)
        @php
            $data = array(
                'd_from' 		=> $startDate,
                'd_to'			=> $endDate,
                'dateSelect'	=> $dateSelect,
            );
        @endphp
    @endif
    @include('report.options.dates')

@endsection
<style>
    #loading_spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
    }

    #loading_spinner svg {
        width: 300px;
        height: 300px;
        color: #fff;
    }

</style>
@section('table')
    <div class="table_wrap">
        <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
            <thead>
            <tr>
                <th class="value_span9">Country</th>
                <th class="value_span9">Clicks</th>
                <th class="value_span9">Unique Clicks</th>
                <th class="value_span9">Conversions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
            @endphp
            @foreach($reports as $key => $row)
                <tr role="row">
                    <td>{{$key}}</td>
                    <td>{{$row['total_clicks']}}</td>
                    <td>{{$row['unique_clicks']}}</td>
                    <td>
                        @if ($row['total_conversions'] > 0 && (Session::userType() == Privilege::ROLE_GOD || Session::userType() == Privilege::ROLE_ADMIN))
                            <a class='load_click' href="/report/geo-by-offer?{{$params}}&country={{$key}}">{{$row['total_conversions']}}</a>
                        @else
                            {{$row['total_conversions']}}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div id="loading_spinner">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="3" r="0">
                <animate id="spinner_318l" begin="0;spinner_cvkU.end-0.5s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
            </circle>
            <circle cx="16.50" cy="4.21" r="0">
                <animate id="spinner_g5Gj" begin="spinner_318l.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
            </circle>
            <circle cx="7.50" cy="4.21" r="0"><animate id="spinner_cvkU" begin="spinner_Uuk0.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="19.79" cy="7.50" r="0"><animate id="spinner_e8rM" begin="spinner_g5Gj.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="4.21" cy="7.50" r="0"><animate id="spinner_Uuk0" begin="spinner_z7ol.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="21.00" cy="12.00" r="0">
                <animate id="spinner_MooL" begin="spinner_e8rM.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="3.00" cy="12.00" r="0"><animate id="spinner_z7ol" begin="spinner_KEoo.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="19.79" cy="16.50" r="0"><animate id="spinner_btyV" begin="spinner_MooL.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle><circle cx="4.21" cy="16.50" r="0">
                <animate id="spinner_KEoo" begin="spinner_1IYD.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="16.50" cy="19.79" r="0"><animate id="spinner_1sIS" begin="spinner_btyV.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="7.50" cy="19.79" r="0"><animate id="spinner_1IYD" begin="spinner_NWhh.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
            <circle cx="12" cy="21" r="0">
                <animate id="spinner_NWhh" begin="spinner_1sIS.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
            </circle>
        </svg>
    </div>
@endsection
@section('footer')
    <script type="text/javascript">
        $(document).ready(function() {
            const loadClick = document.getElementsByClassName('load_click');
            for (const item of loadClick)
            {
                item.addEventListener('click', function() {
                    document.getElementById('loading_spinner').style.display = 'flex';
                });
            }
        });
    </script>
@endsection
