<?php

namespace App\Services;

use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;
use Yajra\DataTables\DataTables;

class UserService {

	public function __construct() {

	}

	public function getUsersDataTable($users) {
		$EDIT_AFFILIATES = Session::permissions()->can( Permissions::EDIT_AFFILIATES);
		$CREATE_AFFILIATES = Session::permissions()->can(Permissions::CREATE_AFFILIATES);
		$CREATE_MANAGERS = Session::permissions()->can(Permissions::CREATE_MANAGERS);
		$role = request('role',3);

		return DataTables::of($users)->addColumn('actions', function ($row) use ($EDIT_AFFILIATES, $CREATE_AFFILIATES, $CREATE_MANAGERS, $role) {
			$html = "";
			if ($EDIT_AFFILIATES){
				$html .= "<a class='btn btn-default btn-sm value_span6-1 value_span4 ' data-toggle='tooltip' title='Edit User' href='/aff_update.php?idrep=" . $row->idrep . "'>Edit</a>";
			}
			if($CREATE_AFFILIATES) {
				$html .= "<a class='btn btn-default btn-sm value_span5-1 ' data-toggle='tooltip' title='Login into this user' href='#' onclick='adminLogin('$row->idrep')'>Login</a>";
			}
			if($CREATE_MANAGERS && $role == 2) {
				$html .= "<a class='btn btn-sm btn-default value_span5-1 ' data-toggle='tooltip' title='View Agents' href='/user/" . $row->idrep . "/affiliates'>View Agents</a>";
			}
			return $html;
		})->rawColumns(['idrep', 'user_name', 'actions', 'referrer_repid','rep_timestamp'])->make(true);
	}
}