<?php

Route::group(['namespace' => 'Abs\UserPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'user-pkg'], function () {
	Route::get('/users/get-list', 'UserController@getUserList')->name('getUserList');
	Route::get('/user/get-form-data/{id?}', 'UserController@getUserFormData')->name('getUserFormData');
	Route::post('/user/save', 'UserController@saveUser')->name('saveUser');
	Route::get('/user/delete/{id}', 'UserController@deleteUser')->name('deleteUser');

});