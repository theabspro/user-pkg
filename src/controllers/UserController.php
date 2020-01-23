<?php

namespace Abs\UserPkg;
use Abs\UserPkg\User;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class UserController extends Controller {

	public function __construct() {
	}

	public function getUserList(Request $request) {
		$users = User::withTrashed()
			->select(
				'users.id',
				'users.name',
				DB::raw('IF(users.mobile_no IS NULL,"--",users.mobile_no) as mobile_no'),
				DB::raw('IF(users.email IS NULL,"--",users.email) as email'),
				DB::raw('IF(users.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('users.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->user_code)) {
					$query->where('users.code', 'LIKE', '%' . $request->user_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->user_name)) {
					$query->where('users.name', 'LIKE', '%' . $request->user_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_no)) {
					$query->where('users.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('users.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->orderby('users.id', 'desc');

		return Datatables::of($users)
			->addColumn('action', function ($user) {
				$edit = asset('public/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/img/content/table/edit-yellow-active.svg');
				$delete = asset('/public/img/content/table/delete-default.svg');
				$delete_active = asset('/public/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-user')) {
					$action .= '<a href="#!/customer-channel-pkg/customer-channel-group/edit/' . $user->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('delete-user')) {
					$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_user"
					onclick="angular.element(this).scope().deleteUser(' . $user->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
					</a>
					';
				}
				return $action;
			})
			->make(true);
	}

	public function getUserFormData($id = NULL) {
		if (!$id) {
			$user = new User;
			$action = 'Add';
		} else {
			$user = User::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['user'] = $user;
		$this->data['action'] = $action;

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
				'mobile_number.unique' => 'Mobile Number is already taken',
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
					'unique:users,username',
				],
				'email' => [
					'nullable:true',
					'max:191',
					'unique:users,email,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'mobile_number' => [
					'nullable:true',
					'max:191',
					'unique:users,mobile_number,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
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
			$user->fill($request->all());
			$user->company_id = Auth::user()->company_id;
			$user->entity_type = 1;
			$user->username = $request->username;
			$user->password = Hash::make($request->password);
			if ($request->status == 'Inactive') {
				$user->deleted_at = Carbon::now();
				$user->deleted_by = Auth::user()->id;
			} else {
				$user->deleted_by = NULL;
				$user->deleted_at = NULL;
			}
			$user->save();

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
	public function deleteUser($id) {
		$delete_status = User::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
