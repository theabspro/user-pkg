<?php

namespace Abs\UserPkg;
use Abs\UserPkg\User;
use App\Address;
use App\Country;
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
				'users.code',
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
			->addColumn('code', function ($user) {
				$status = $user->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $user->code;
			})
			->addColumn('action', function ($user) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/user-pkg/user/edit/' . $user->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_user"
					onclick="angular.element(this).scope().deleteUser(' . $user->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getUserFormData($id = NULL) {
		if (!$id) {
			$user = new User;
			$address = new Address;
			$action = 'Add';
		} else {
			$user = User::withTrashed()->find($id);
			$address = Address::where('address_of_id', 24)->where('entity_id', $id)->first();
			if (!$address) {
				$address = new Address;
			}
			$action = 'Edit';
		}
		$this->data['country_list'] = $country_list = Collect(Country::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Country']);
		$this->data['user'] = $user;
		$this->data['address'] = $address;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveUser(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'User Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'User Code is already taken',
				'name.required' => 'User Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'gst_number.required' => 'GST Number is Required',
				'gst_number.max' => 'Maximum 191 Numbers',
				'mobile_no.max' => 'Maximum 25 Numbers',
				// 'email.required' => 'Email is Required',
				'address_line1.required' => 'Address Line 1 is Required',
				'address_line1.max' => 'Maximum 255 Characters',
				'address_line1.min' => 'Minimum 3 Characters',
				'address_line2.max' => 'Maximum 255 Characters',
				// 'pincode.required' => 'Pincode is Required',
				// 'pincode.max' => 'Maximum 6 Characters',
				// 'pincode.min' => 'Minimum 6 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:users,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => 'required|max:255|min:3',
				'gst_number' => 'required|max:191',
				'mobile_no' => 'nullable|max:25',
				// 'email' => 'nullable',
				'address' => 'required',
				'address_line1' => 'required|max:255|min:3',
				'address_line2' => 'max:255',
				// 'pincode' => 'required|max:6|min:6',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$user = new User;
				$user->created_by_id = Auth::user()->id;
				$user->created_at = Carbon::now();
				$user->updated_at = NULL;
				$address = new Address;
			} else {
				$user = User::withTrashed()->find($request->id);
				$user->updated_by_id = Auth::user()->id;
				$user->updated_at = Carbon::now();
				$address = Address::where('address_of_id', 24)->where('entity_id', $request->id)->first();
			}
			$user->fill($request->all());
			$user->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$user->deleted_at = Carbon::now();
				$user->deleted_by_id = Auth::user()->id;
			} else {
				$user->deleted_by_id = NULL;
				$user->deleted_at = NULL;
			}
			$user->gst_number = $request->gst_number;
			$user->axapta_location_id = $request->axapta_location_id;
			$user->save();

			if (!$address) {
				$address = new Address;
			}
			$address->fill($request->all());
			$address->company_id = Auth::user()->company_id;
			$address->address_of_id = 24;
			$address->entity_id = $user->id;
			$address->address_type_id = 40;
			$address->name = 'Primary Address';
			$address->save();

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
			$address_delete = Address::where('address_of_id', 24)->where('entity_id', $id)->forceDelete();
			return response()->json(['success' => true]);
		}
	}
}
