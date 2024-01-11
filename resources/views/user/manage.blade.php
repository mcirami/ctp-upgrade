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
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->idrep}}</td>
                            <td class="username">{{$user->user_name}}</td>
                            <td class="actions">
                                @if(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_AFFILIATES))
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4 " data-toggle="tooltip" title="Edit User"
                                       href="/aff_update.php?idrep={{$user->idrep}}">Edit</a>
                                @endif
                                @if(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_AFFILIATES))
                                    <a class="btn btn-default btn-sm value_span5-1 " data-toggle="tooltip"
                                       title="Login into this user" href="#" onclick="adminLogin({{$user->idrep}})">Login</a>
                                @endif
                                @if(request('role',3) == 2 && \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_MANAGERS))
                                    <a class="btn btn-sm btn-default value_span5-1" data-toggle="tooltip" title="View Agents"
                                       href="/user/{{$user->idrep}}/affiliates">View Agents</a>
                                @endif
                            </td>
                            <td>{{$user->referrer->user_name}}</td>
                            <td>{{\Carbon\Carbon::parse($user->rep_timestamp)->diffForHumans()}}</td>
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

