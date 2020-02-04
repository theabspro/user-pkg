<?php
namespace Abs\UserPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class UserPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//USERS
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'users',
				'display_name' => 'Users',
			],
			[
				'display_order' => 1,
				'parent' => 'users',
				'name' => 'add-user',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'users',
				'name' => 'edit-user',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'users',
				'name' => 'view-user',
				'display_name' => 'View',
			],
			[
				'display_order' => 4,
				'parent' => 'users',
				'name' => 'delete-user',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);

	}
}