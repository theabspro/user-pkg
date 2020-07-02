<?php

namespace Abs\UserPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use App\Role;
use DB;
use Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

// use App\BaseModel;

class User extends Authenticatable {
	use HasApiTokens;
	use Notifiable;
	use EntrustUserTrait;
	use SeederTrait;
	use SoftDeletes;
	// use SoftDeletes {
	// 	SoftDeletes::restore insteadof EntrustUserTrait;
	// 	EntrustUserTrait::restore insteadof SoftDeletes;
	// }

	protected $table = 'users';
	public $timestamps = true;
	protected $fillable = [
		'company_id',
		'user_type_id',
		'entity_id',
		'first_name',
		'last_name',
		'username',
		'email',
		'personal_email',
		'alternate_mobile_number',
		'dob',
		'mobile_number',
		'force_password_reset',
		'password',
		'imei',
		'otp',
		'mpin',
		'profile_image_id',
		//'invitation_sent',
		'slack_api_url',
	];

	protected static $excelColumnRules = [
		'User Type Name' => [
			'table_column_name' => 'user_type_id',
			'rules' => [
				'fk' => [
					'class' => 'App\Config',
					'foreign_table_column' => 'name',
					// 'fill' => true,
				],
			],
		],
		'First Name' => [
			'table_column_name' => 'first_name',
			'rules' => [
				'required' => [
					// 'fill' => true,
				],
			],
		],
		'Last Name' => [
			'table_column_name' => 'last_name',
			'rules' => [
				'nullable' => [
					// 'fill' => true,
				],
			],
		],
		'Email' => [
			'table_column_name' => 'email',
			'rules' => [
				'nullable' => [
				],
				'email' => [
					// 'fill' => true,
				],
			],
		],
		'Mobile Number' => [
			'table_column_name' => 'mobile_number',
			'rules' => [
				'nullable' => [
				],
				'mobile_number' => [
					// 'fill' => true,
				],
			],
		],
		'Username' => [
			'table_column_name' => 'username',
			'rules' => [
				'required' => [
					// 'fill' => true,
				],
			],
		],
		'Password' => [
			'table_column_name' => 'password',
			'rules' => [
				'nullable' => [
					// 'fill' => true,
				],
			],
		],
		'Personal Email' => [
			'table_column_name' => 'personal_email',
			'rules' => [
				'nullable' => [
				],
				'email' => [
					// 'fill' => true,
				],
			],
		],
		'Alternate Mobile Number' => [
			'table_column_name' => 'alternate_mobile_number',
			'rules' => [
				'nullable' => [
				],
				'mobile_number' => [
					// 'fill' => true,
				],
			],
		],
		'DOB' => [
			'table_column_name' => 'dob',
			'rules' => [
				'nullable' => [
				],
				'date' => [
					'format' => 'd M Y',
					// 'fill' => true,
					// 'self_table_column' => 'dob',
				],
			],
		],
		'Force Password Reset' => [
			'table_column_name' => 'force_password_reset',
			'rules' => [
				'nullable' => [
				],
				'boolean' => [
					// 'fill' => true,
				],
			],
		],
		'Has Mobile Login' => [
			'table_column_name' => 'has_mobile_login',
			'rules' => [
				'nullable' => [
				],
				'boolean' => [
					// 'fill' => true,
				],
			],
		],
		'IMEI' => [
			'table_column_name' => 'imei',
			'rules' => [
				'nullable' => [
					// 'fill' => true,
					// 'self_table_column' => 'imei',
				],
			],
		],
		'OTP' => [
			'table_column_name' => 'otp',
			'rules' => [
				'nullable' => [
					// 'fill' => true,
					// 'self_table_column' => 'otp',
				],
			],
		],
		'MPIN' => [
			'table_column_name' => 'mpin',
			'rules' => [
				'nullable' => [
					// 'fill' => true,
					// 'self_table_column' => 'mpin',
				],
			],
		],
		'Invitation Sent' => [
			'table_column_name' => 'invitation_sent',
			'rules' => [
				'nullable' => [
				],
				'boolean' => [
					// 'fill' => true,
				],
			],
		],

	];

	protected $hidden = [
		'password', 'remember_token',
	];

	protected $relationships = [
		'type',
		'outlet',
		'vehicle',
		'vehicle.model',
		'customer',
		'serviceType',
		'status',
	];

	// Relationships --------------------------------------------------------------

	public function company() {
		return $this->belongsTo('App\Company');
	}

	public function roles() {
		return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
	}

	public function profileImage() {
		return $this->hasOne('App\Attachment', 'entity_id')->where('attachment_of_id', 120)->where('attachment_type_id', 140);
	}

	public function profileImageUrl() {
		return $this->profileImage ? './storage/app/public/user-profile-images/' . $this->profileImage->name : '';
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
	// Getter & Setters --------------------------------------------------------------

	public function setPasswordAttribute($pass) {
		$this->attributes['password'] = Hash::make($pass);
	}

	public function getDobAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function setDobAttribute($date) {
		return $this->attributes['dob'] = empty($date) ? NULL : date('Y-m-d', strtotime($date));
	}

	// Static Operations --------------------------------------------------------------

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

		$record = static::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function mapRoles($records, $company = null, $specific_company = null, $tc) {
		$success = 0;
		$error_records = [];
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company_code) {
					continue;
				}
				$status = static::mapRole($record_data);
				if (!$status['success']) {
					$error_records[] = array_merge($record_data->toArray(), [
						'Record No' => $key + 1,
						'Errors' => implode(',', $status['errors']),
					]);
					continue;
				}
				$success++;
			} catch (Exception $e) {
				dump($e);
			}
		}
		dump($success . ' Records Processed');
		dump(count($error_records) . ' Errors');
		dump($error_records);
		return $error_records;
	}

	public static function mapRole($record_data) {
		$errors = [];

		$company = Company::where('code', $record_data->company_code)->first();
		if (!$company) {
			$errors[] = 'Invalid Company : ' . $record_data->company_code;
		}

		$user = User::where('username', $record_data->username)->where('company_id', $company->id)->first();
		if (!$user) {
			$errors[] = 'Invalid user : ' . $record_data->username;
		}

		$role = Role::where('name', $record_data->role_name)->first();
		if (!$role) {
			$errors[] = 'Invalid role : ' . $record_data->role_name;
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
			return;
		}

		$user->roles()->syncWithoutDetaching([$role->id]);
		return [
			'success' => true,
		];
	}

	public static function getList($type_id, $add_default = true, $default_text = 'Select User') {
		$list = Collect(User::select([
			'id',
			'first_name',
			'first_name as name',
			'email',
			DB::raw("'http://www.gravatar.com/avatar/90c5367acf89e67f39d6f87d36546e13?s=50&d=retro' as image"),
		])->where([
			'user_type_id' => $type_id,
		])->orderBy('first_name')->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'first_name' => $default_text]);
		}
		return $list;
	}

	// Static Operations --------------------------------------------------------------

	public static function saveFromExcelArray($record_data) {
		try {
			$errors = [];

			$company = Company::where('code', $record_data['Company Code'])->first();
			if (!$company) {
				return [
					'success' => false,
					'errors' => ['Invalid Company : ' . $record_data['Company Code']],
				];
			}

			if (!isset($record_data['created_by_id'])) {
				$admin = $company->admin();

				if (!$admin) {
					return [
						'success' => false,
						'errors' => ['Default Admin user not found'],
					];
				}
				$created_by_id = $admin->id;
			} else {
				$created_by_id = $record_data['created_by_id'];
			}

			if (empty($record_data['Username'])) {
				$errors[] = 'Userame is empty';
			}

			if (count($errors) > 0) {
				return [
					'success' => false,
					'errors' => $errors,
				];
			}

			$record = Self::firstOrNew([
				'company_id' => $company->id,
				'username' => $record_data['Username'],
			]);
			$result = Self::validateAndFillExcelColumns($record_data, Self::$excelColumnRules, $record);
			if (!$result['success']) {
				return $result;
			}
			$record->created_by_id = $created_by_id;
			$record->save();
			return [
				'success' => true,
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'errors' => ['Exception Error : ' . $e->getMessage() . '. Line : ' . $e->getLine() . '. File : ' . $e->getFile()],
			];
		}
	}

	public static function importFromJob($job) {

		try {
			$response = ImportCronJob::getRecordsFromExcel($job, 'N');
			$rows = $response['rows'];
			$header = $response['header'];

			$all_error_records = [];
			foreach ($rows as $k => $row) {
				$record = [];
				foreach ($header as $key => $column) {
					if (!$column) {
						continue;
					} else {
						$record[$column] = trim($row[$key]);
					}
				}
				$original_record = $record;
				$record['Company Code'] = $job->company->code;
				$record['created_by_id'] = $job->created_by_id;
				$result = static::saveFromExcelArray($record);
				if (!$result['success']) {
					$original_record['Record No'] = $k + 1;
					$original_record['Error Details'] = implode(',', $result['errors']);
					$all_error_records[] = $original_record;
					$job->incrementError();
					continue;
				}

				$job->incrementNew();

				DB::commit();
				//UPDATING PROGRESS FOR EVERY FIVE RECORDS
				if (($k + 1) % 5 == 0) {
					$job->save();
				}
			}

			//COMPLETED or completed with errors
			$job->status_id = $job->error_count == 0 ? 7202 : 7205;
			$job->save();

			ImportCronJob::generateImportReport([
				'job' => $job,
				'all_error_records' => $all_error_records,
			]);

		} catch (\Throwable $e) {
			$job->status_id = 7203; //Error
			$job->error_details = 'Error:' . $e->getMessage() . '. Line:' . $e->getLine() . '. File:' . $e->getFile(); //Error
			$job->save();
			dump($job->error_details);
		}

	}

	public static function saveFromObject($record_data) {

		$record = [
			'Company Code' => $record_data->company_code,
			'User Type Name' => $record_data->user_type_name,
			'Entity Code' => $record_data->entity_code,
			'First Name' => $record_data->first_name,
			'Last Name' => $record_data->last_name,
			'Email' => $record_data->email,
			'Mobile Number' => $record_data->mobile_number,
			'Username' => $record_data->username,
			'Password' => $record_data->password,
			'Personal Email' => $record_data->personal_email,
			'Alternate Mobile Number' => $record_data->alternate_mobile_number,
			'DOB' => $record_data->dob,
			'Force Password Reset' => $record_data->force_password_reset,
			'Has Mobile Login' => $record_data->has_mobile_login,
			'IMEI' => $record_data->imei,
			'OTP' => $record_data->otp,
			'MPIN' => $record_data->mpin,
			'Invitation Sent' => $record_data->invitation_sent,
		];
		return static::saveFromExcelArray($record);
	}

}
