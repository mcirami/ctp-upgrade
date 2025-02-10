@php

    $filterValue = isset($_GET['filter']) ? $_GET['filter'] : "affiliate";

@endphp

<select name="filter" id="filter" class="selectBox" onchange="getConversionsView(this);" style="width: 170px; margin-bottom: 20px;">
    <option value="affiliate" @php if($filterValue == "affiliate") { echo "selected"; } @endphp>Affiliate View</option>
    <option value="country" @php if($filterValue == "country") { echo "selected"; } @endphp>Country View</option>
</select>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        function getConversionsView(element) {
            let data = <?php echo json_encode($data); ?>;
            console.log(data);

            if (element.value == "affiliate") {
                $slug = "user-conversions";
                $filter = "affiliate";
            } else {
                $slug = "conversions-by-country";
                $filter = "country";
            }

            window.location.href = '/report/offer/' + 
            data.offerId + '/' +
            $slug +
            '?filter=' + $filter + 
            '&d_from=' + data.d_from + 
            '&d_to=' + data.d_to + 
            '&dateSelect=' + data.dateSelect;
        }

        window.getConversionsView = getConversionsView;
    });
</script>