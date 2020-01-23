<?php

Route::group(['namespace' => 'Abs\UserPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'user-pkg'], function () {
	Route::get('/users/get-list', 'UserController@getUserPkgList')->name('getUserPkgList');
	Route::get('/user/get-form-data/{id?}', 'UserController@getUserFormData')->name('getUserFormData');
	Route::post('/user/save', 'UserController@saveUser')->name('saveUser');
	Route::get('/user/delete/{id}', 'UserController@deleteUser')->name('deleteUser');
	Route::get('/user/view/{user_id}', 'UserController@viewFormData')->name('viewFormData');
});