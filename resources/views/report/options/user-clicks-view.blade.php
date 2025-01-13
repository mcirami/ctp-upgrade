@php

    $filterValue = isset($_GET['filter']) ? $_GET['filter'] : "time";

@endphp

<select name="filter" id="filter" class="selectBox" onchange="getConversionsView(this);" style="width: 170px; margin-bottom: 20px;">
    <option value="time" @php if($filterValue == "time") { echo "selected"; } @endphp>Time View</option>
    <option value="country" @php if($filterValue == "country") { echo "selected"; } @endphp>Country View</option>
</select>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        function getConversionsView(element) {
            let data = <?php echo json_encode($data); ?>;
            console.log(data);

            if (element.value == "time") {
                $slug = "conversions";
                $filter = "time";
            } else {
                $slug = "conversions-by-country";
                $filter = "country";
            }

            window.location.href = '/user/' + 
            data.user + '/' + 
            $slug +
            '?filter=' + $filter + 
            '&d_from=' + data.d_from + 
            '&d_to=' + data.d_to + 
            '&dateSelect=' + data.dateSelect + 
            "&offer=" + data.offerId;
        }

        window.getConversionsView = getConversionsView;
    });
</script>