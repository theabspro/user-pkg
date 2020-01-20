<?php
Route::group(['namespace' => 'Abs\UserPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'user-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});