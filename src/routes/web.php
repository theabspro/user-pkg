<?php

Route::group(['namespace' => 'Abs\UserPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'user-pkg'], function () {
	Route::get('/users/get-list', 'UserController@getUserPkgList')->name('getUserPkgList');
	Route::get('/user/get-form-data', 'UserController@getUserFormData')->name('getUserFormData');
	Route::post('/user/save', 'UserController@saveUser')->name('pkgSaveUser');
	Route::get('/user/delete', 'UserController@deleteUser')->name('deleteUser');
	Route::get('/user/view', 'UserController@viewFormData')->name('viewFormData');
});