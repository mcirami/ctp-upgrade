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
                <input id="searchBox"
                       class="form-control"
                       type="text"
                       placeholder="Search By Username, Email or ID" />
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
                </table>
            </div>
        </div>


    </div>
    <!--right_panel-->

@endsection

@section('footer')

        @push('scripts')
            <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
            <script type="text/javascript">

            $(document).ready(function () {
                const table = $('#mainTable').DataTable({
                    ajax: {
                        url: '/user/get-all-users',
                        dataSrc: 'data',
                    },
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
                    columns: [
                        {data: 'idrep', name: 'idrep'},
                        {data: 'user_name', name: 'user_name'},
                        {data: 'actions', name: 'actions'},
                        {data: 'referrer_repid', name: 'referrer_repid'},
                        {data: 'rep_timestamp', name: 'rep_timestamp'},
                    ],
                    initComplete: function (settings, json) {
                        // This callback runs every time the table is redrawn (including paging)
                        $('.dt-paging .dt-paging-button.current').addClass('button active value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4');
                        $('.dt-paging .dt-paging-button:not(.current)').addClass('button value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4')
                        // You could also conditionally add different classes for first/last/etc
                    },
                    drawCallback: function (settings) {
                        // This callback runs every time the table is redrawn (including paging)
                        $('.dt-paging .dt-paging-button.current').addClass('button active value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4');
                        $('.dt-paging .dt-paging-button:not(.current)').addClass('button value_span2-2 value_span3-2 value_span6-1 value_span2 value_span2-2 value_span4')
                        // You could also conditionally add different classes for first/last/etc
                    },
                    stateLoadCallback: function (settings, callback) {
                        console.log('state callback');
                    }
                });

                $(window).on('resize', function() {
                    // forcibly re-draw or re-apply classes
                    // table.draw(false) will re-draw while maintaining paging
                    table.draw(false);
                });

            });
        </script>
    @endpush
@endsection

