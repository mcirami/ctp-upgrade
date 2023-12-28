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
                <table class="table table-striped  table_01  " id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span8">User ID</th>
                        <th class="value_span8">First Name</th>
                        <th class="value_span8">Last Name</th>
                        <th class="value_span8">Cell Phone</th>
                        <th class="value_span8">Username</th>
                        <th class="value_span8">Status</th>
                        <th class="value_span8">Referrer User Name</th>
                        <th class="value_span8">Timestamp</th>
                        <th class="value_span8">Actions</th>
                        <th></th>

                        @if (request('role',3) == 2)
                            <th></th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->idrep}}</td>
                            <td>{{$user->first_name}}</td>
                            <td>{{$user->last_name}}</td>
                            <td>{{$user->cell_phone}}</td>
                            <td>{{$user->user_name}}</td>
                            <td>{{$user->status}}</td>
                            <td>{{$user->referrer->user_name}}</td>
                            <td>{{\Carbon\Carbon::parse($user->rep_timestamp)->diffForHumans()}}</td>
                            @if(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_AFFILIATES))
                                <td>
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4 " data-toggle="tooltip" title="Edit User"
                                       href="/aff_update.php?idrep={{$user->idrep}}">Edit</a>
                                </td>
                            @endif
                            @if(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_AFFILIATES))
                                <td><a class="btn btn-default btn-sm value_span5-1 " data-toggle="tooltip"
                                       title="Login into this user" href="#" onclick="adminLogin({{$user->idrep}})">Login</a>
                                </td>
                            @endif
                            @if(request('role',3) == 2 && \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_MANAGERS))
                                <td>
                                    <a class="btn btn-sm btn-default value_span5-1" data-toggle="tooltip" title="View Agents"
                                       href="/user/{{$user->idrep}}/affiliates">View Agents</a>
                                </td>
                            @endif
                            @if(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::BAN_USERS))
                                <td>
                                    <a class="btn btn-default btn-sm value_span11 value_span4 " data-toggle="tooltip" title="Ban User"
                                       href="/ban_user.php?uid={{$user->idrep}}">Ban User</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
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
        });
    </script>
@endsection

