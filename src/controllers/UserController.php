<?php

namespace Abs\UserPkg;
use Abs\RolePkg\Role;
use Abs\UserPkg\User;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Hash;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class UserController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getUserPkgList(Request $request) {
		$users = User::withTrashed()
			->select(
				'users.id',
				'users.first_name as name',
				'users.last_name',
				'users.username',
				DB::raw('COALESCE(users.mobile_number,"--") as mobile_number'),
				DB::raw('COALESCE(users.email,"--") as email'),
				DB::raw('IF(users.deleted_at IS NULL,"Active","Inactive") as status'),
				DB::raw('COUNT(role_user.user_id) as roles_count')
			)
			->leftJoin('role_user', 'users.id', 'role_user.user_id')
			->where('users.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('users.first_name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->username)) {
					$query->where('users.username', 'LIKE', '%' . $request->username . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_number)) {
					$query->where('users.mobile_number', 'LIKE', '%' . $request->mobile_number . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('users.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('users.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('users.deleted_at');
				}
			})
			->groupby('users.id')
			->orderby('users.id', 'desc')
		// ->get()
		;

		// dd($users);

		return Datatables::of($users)
			->addColumn('name', function ($users) {
				$status = $users->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $users->name;
			})
			->addColumn('action', function ($user) {
				$edit = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-user')) {
					$action .= '<a href="#!/user-pkg/user/edit/' . $user->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				/*
					if (Entrust::can('view-user')) {
						$action .= '<a href="#!/user-pkg/user/view/' . $user->id . '">
							<img src="' . $view . '" alt="View" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '" >
						</a>';

					}
					if (Entrust::can('delete-user')) {
						$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_user"
						onclick="angular.element(this).scope().deleteUser(' . $user->id . ')" dusk = "delete-btn" title="Delete">
						<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
						</a>
						';
					}
				*/
				return $action;
			})
			->make(true);
	}

	public function getUserFormData(Request $request) {
		$id = $request->id;
		$user_roles = [];
		if (!$id) {
			$user = new User;
			$action = 'Add';
		} else {
			$user = User::withTrashed()->find($id);
			$action = 'Edit';
			$user->roles;
			if ($user->roles) {
				foreach ($user->roles as $key => $role) {
					$user_roles[] = $role->id;
				}
			}
		}

		$this->data['user'] = $user;
		$this->data['user_roles'] = $user_roles;
		$this->data['action'] = $action;
		$this->data['role_list'] = $role_list = Role::select('name', 'id')->get();
		$this->data['theme'];
		return response()->json($this->data);
	}

	public function viewFormData(Request $request) {
		$this->data['user'] = $user = User::withTrashed()->find($request->id);
		$user->roles;
		$this->data['action'] = 'View';
		$this->data['theme'];
		return response()->json($this->data);
	}

	public function saveUser(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'username.required' => 'User Name is Required',
				'username.max' => 'Maximum 191 Characters',
				'username.min' => 'Minimum 3 Characters',
				'username.unique' => 'User Name is already taken',
				'email.unique' => 'User Name is already taken',
				'contact_number.unique' => 'Mobile Number is already taken',
				'imei.max' => 'Maximum 15 Characters',
				'otp.max' => 'Maximum 6 Characters',
				'mpin.max' => 'Maximum 10 Characters',
			];
			$validator = Validator::make($request->all(), [
				'name' => 'required:true|max:255|min:3',
				'username' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:users,username,' . $request->id . ',id',
				],
				'email' => [
					'nullable:true',
					'max:191',
					'unique:users,email,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'contact_number' => [
					'nullable:true',
					'max:10',
					'unique:users,contact_number,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'password' => 'nullable',
				'imei' => 'nullable|max:15',
				'otp' => 'nullable|max:6',
				'mpin' => 'nullable|max:10',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$user = new User;
				$user->created_by = Auth::user()->id;
				$user->created_at = Carbon::now();
				$user->updated_at = NULL;
			} else {
				$user = User::withTrashed()->find($request->id);
				$user->updated_by = Auth::user()->id;
				$user->updated_at = Carbon::now();
			}
			$user->company_id = Auth::user()->company_id;
			// $user->entity_type = 1;
			$user->fill($request->all());
			// dd($user);
			if ($request->status == 'Inactive') {
				$user->deleted_at = Carbon::now();
				$user->deleted_by = Auth::user()->id;
			} else {
				$user->deleted_by = NULL;
				$user->deleted_at = NULL;
			}
			if ($request->change_password == '1') {
				$user->password = Hash::make($request->password);
			}
			$user->save();

			$user->roles()->sync(json_decode($request->roles_id));

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['User Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['User Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteUser(Request $request) {
		$delete_status = User::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
