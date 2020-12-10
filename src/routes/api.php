<?php

use App\Http\Controllers\Api\Masters\Auth\UserController;

Route::group(['middleware' => ['auth:api'], 'prefix' => '/api/masters/auth/user'], function () {
	$className = UserController::class;
	Route::get('index', $className . '@index');
	Route::get('read/{id}', $className . '@read');
	Route::post('save', $className . '@save');
	Route::get('options', $className . '@options');
	Route::get('delete/{user}', $className . '@delete');
});
