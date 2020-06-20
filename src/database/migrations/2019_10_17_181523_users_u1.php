<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'name') && Schema::hasColumn('users', 'company_id')) {

				$table->dropColumn('name');

				$table->unsignedInteger('company_id')->after('id');
				$table->unsignedInteger('user_type_id')->nullable()->after('company_id');
				$table->unsignedInteger('entity_id')->nullable()->after('user_type_id');
				$table->string('first_name', 32)->nullable()->after('entity_id');
				$table->string('last_name', 32)->nullable()->after('first_name');
				$table->string('username', 32)->after('last_name');
				$table->string('mobile_number', 12)->nullable()->after('email');
				$table->boolean('force_password_reset')->default(0)->after('password');
				$table->boolean('has_mobile_login')->after('force_password_reset');
				$table->string('imei', 64)->nullable()->after('has_mobile_login');
				$table->string('otp', 6)->nullable()->after('imei');
				$table->string('mpin', 4)->nullable()->after('otp');
				$table->unsignedInteger('profile_image_id')->nullable()->after('mpin');
				$table->unsignedInteger('created_by_id')->nullable()->after('remember_token');
				$table->unsignedInteger('updated_by_id')->nullable()->after('created_by_id');
				$table->unsignedInteger('deleted_by_id')->nullable()->after('updated_by_id');
				$table->softdeletes();

				$table->foreign('user_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('profile_image_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');
			}

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('users', function (Blueprint $table) {
			$table->dropForeign('users_company_id_foreign');
			$table->dropForeign('users_user_type_id_foreign');
			$table->dropForeign('users_profile_image_id_foreign');
			$table->dropForeign('users_created_by_id_foreign');
			$table->dropForeign('users_updated_by_id_foreign');
			$table->dropForeign('users_deleted_by_id_foreign');

			$table->dropUnique('users_company_id_username_unique');
			$table->dropUnique('users_company_id_mobile_number_unique');

			$table->dropColumn('company_id');
			$table->dropColumn('user_type_id');
			$table->dropColumn('entity_id');
			$table->dropColumn('first_name');
			$table->dropColumn('last_name');
			$table->dropColumn('username');
			$table->dropColumn('mobile_number');
			$table->dropColumn('force_password_reset');
			$table->dropColumn('has_mobile_login');
			$table->dropColumn('imei');
			$table->dropColumn('otp');
			$table->dropColumn('mpin');
			$table->dropColumn('profile_image_id');
			$table->dropColumn('created_by_id');
			$table->dropColumn('updated_by_id');
			$table->dropColumn('deleted_by_id');
			$table->dropColumn('deleted_at');

			$table->string('name', 32);

		});
	}
}
