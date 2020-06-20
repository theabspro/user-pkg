<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersU2 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('users', function (Blueprint $table) {
			$table->string('personal_email', 64)->nullable()->after('email');
			$table->string('alternate_mobile_number', 10)->nullable()->after('personal_email');
			$table->date('dob')->nullable()->after('alternate_mobile_number');

			if (!Schema::hasColumn('users', 'first_name')) {
				$table->string('first_name', 32)->nullable()->after('entity_id');
			}
			if (!Schema::hasColumn('users', 'last_name')) {
				$table->string('last_name', 32)->nullable()->after('first_name');
			}

			if (!Schema::hasColumn('users', 'mobile_number')) {
				$table->string('mobile_number', 12)->nullable()->after('email');
			}

			if (!Schema::hasColumn('users', 'force_password_reset')) {
				$table->boolean('force_password_reset')->default(0)->after('password');
			}

			if (!Schema::hasColumn('users', 'has_mobile_login')) {
				$table->boolean('has_mobile_login')->after('force_password_reset');
			}

			if (!Schema::hasColumn('users', 'imei')) {
				$table->string('imei', 64)->nullable()->after('has_mobile_login');
			}

			if (!Schema::hasColumn('users', 'otp')) {
				$table->string('otp', 6)->nullable()->after('imei');
			}

			if (!Schema::hasColumn('users', 'mpin')) {
				$table->string('mpin', 4)->nullable()->after('otp');
			}

			if (!Schema::hasColumn('users', 'profile_image_id')) {
				$table->unsignedInteger('profile_image_id')->nullable()->after('mpin');
				$table->foreign('profile_image_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');
			}

			$table->unique('personal_email');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('users', function (Blueprint $table) {
			$table->dropUnique('users_personal_email_unique');
			$table->dropColumn('personal_email');
			$table->dropColumn('alternate_mobile_number');
			$table->dropColumn('dob');
		});
	}
}
