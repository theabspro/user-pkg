<?php

namespace Abs\UserPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable {
	use HasApiTokens;
	use Notifiable;
	use EntrustUserTrait;
	use SeederTrait;
	use SoftDeletes {
		SoftDeletes::restore insteadof EntrustUserTrait;
		EntrustUserTrait::restore insteadof SoftDeletes;
	}

	protected $table = 'users';
	public $timestamps = true;
	protected $fillable = [
		'company_id',
		'entity_type',
		'user_type_id',
		'entity_id',
		'username',
		'name',
		'email',
		'force_password_reset',
		'mobile_number',
		'password',
		'imei',
		'otp',
		'mpin',
		'profile_image',
		'created_at',
		'updated_at',
		'deleted_at',
		'last_login',
		'last_logout',
	];

	protected $hidden = [
		'password', 'remember_token',
	];

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function mapRoles($records) {
		// dd($records);
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::mapRole($record_data);
			} catch (Exception $e) {
				dump($e);
			}
		}
	}

	public static function mapRole($record_data) {
		$company = Company::where('code', $record_data->company)->first();

		$errors = [];
		if (!$company) {
			$errors[] = 'Invalid Company : ' . $record_data->company;
		}
		$user = User::where('username', $record_data->user)->where('company_id', $company->id)->first();
		if (!$user) {
			$errors[] = 'Invalid user : ' . $record_data->user;
		}

		$role = Role::where('name', $record_data->role)->first();
		if (!$role) {
			$errors[] = 'Invalid role : ' . $record_data->role;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$user->roles()->syncWithoutDetaching([$role->id]);
		return $user;
	}

	public function company() {
		return $this->hasOne('App\Company', 'id', 'company_id');
	}
	public function roles() {
		return $this->belongsToMany('Abs\RolePkg\Role', 'role_user', 'user_id', 'role_id');
	}

	public function permissions() {
		$perms = [];
		foreach ($this->roles as $key => $role) {
			foreach ($role->perms as $key2 => $perm) {
				$perms[] = $perm->name;
			}
		}
		return $perms;
	}

	public function perms() {
		$permissions = [];
		foreach ($this->roles as $role) {
			foreach ($role->perms as $permission) {
				$permissions[] = $permission->name;
			}
		}
		return $permissions;
	}

}
