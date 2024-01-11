@extends('layouts.master')

@section('content')
    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer large_table ">
            <div class="heading_holder">
                <span class="lft value_span9">View User Accounts</span>

            </div>

            <div class='form-group '>
                @include('report.options.user-type')
                @include('report.options.active')
            </div>

            <div class="form-group searchDiv">

                <input id="searchBox" onkeyup="searchTable()" class="form-control" type="text"
                       placeholder="Search Selected User Type!">
            </div>

            <div class="clear"></div>
            <div class="white_box_x_scroll white_box manage_aff large_table value_span8 ">
                <table class="table table-striped  table_01 manage_user_table " id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span8">User ID</th>
                        <th class="value_span8">Username</th>
                        <th class="value_span8">Actions</th>
                        <th class="value_span8">Manager</th>
                        <th class="value_span8">Timestamp</th>

                        @if (request('role',3) == 2)
                            <th></th>
                        @endif
                    </tr>
                    </thead>
                    <tbody id="subid_content"></tbody>
                </table>
            </div>
        </div>


    </div>
    <!--right_panel-->

@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[4, 0]],
                    widgets: ['staticRow']
                });

	        const subIds = JSON.parse('<?php echo json_encode($subIds); ?> ');
	        const idrep = '<?php echo $idrep; ?>';
	        displayContent(subIds);

	        document.getElementById('searchBox').addEventListener('input', (e) => {
		        const userInput = e.target.value.trim().toLowerCase();
		        let filteredSubIds = subIds.filter((subId) => {
			        return subId.subId.toLowerCase().includes(userInput);
		        })

		        displayContent(filteredSubIds);

	        });

	        function displayContent(subIds) {

		        let html = "";
		        subIds.forEach((subId) => {
			        html += "<tr>" +
				        "<td>" + subId['subId'] + "</td>" +
				        "<td class='button_wrap'>";
			        if (subId["blocked"]) {
				        html += "<button class='block_sub_id' disabled='disabled'" +
					        " data-subid='" + subId["subId"] + "'" +
					        " data-rep='" + idrep + "'" +
					        ">Blocked</button>" +
					        "<button class='unblock_button value_span6-2 value_span2 value_span1-2'" +
					        " data-subid='" + subId["subId"] + "'" +
					        " data-rep='" + idrep + "'>UnBlock</button>";
			        } else {
				        html += "<button class='block_sub_id value_span6-2 value_span2 value_span1-2'" +
					        " data-subid='" + subId["subId"] + "'" +
					        " data-rep='" + idrep + "'>Block ID</button>" +
					        "<button style='display: none;'" +
					        " disabled='disabled'" +
					        " class='unblock_button value_span6-2 value_span2 value_span1-2'" +
					        " data-subid='" + subId["subId"] + "'" +
					        " data-rep='" + idrep +"'" +
					        ">UnBlock</button>";
			        }

			        html += "</td></tr>";
		        })

		        document.getElementById('subid_content').innerHTML = html;
	        }
        });
    </script>
@endsection

