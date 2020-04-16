<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersU1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
             $table->string('personal_email',64)->nullable()->after('mobile_number');
             $table->string('alternate_mobile_number',10)->nullable()->after('personal_email');
             $table->date('dob')->nullable()->after('alternate_mobile_number');
             $table->unique('personal_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('users', function (Blueprint $table) {
           $table->dropUnique('users_personal_email_unique');
           $table->dropColumn('personal_email');
           $table->dropColumn('alternate_mobile_number');
           $table->dropColumn('dob');
        });
    }
}
