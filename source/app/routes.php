<?php

Route::get('/', ['as' => 'home', 'uses' => 'CertsController@redirect']);
Route::get('login', ['as' => 'auth-login-path', 'uses' => 'AuthController@showLogin']);
Route::post('login', ['uses' => 'AuthController@doLogin']);
Route::get('rootCA/install', ['as' => 'root-ca-install-path', 'uses' => 'CertsController@rootCAInstall']);

Route::group(['before' => 'auth'], function() {
	Route::get('certs', ['as' => 'certs-path', 'uses' => 'CertsController@index']);
	Route::post('certs', ['as' => 'create-cert-path', 'uses' => 'CertsController@store']);
	Route::post('cert/sign', ['as' => 'sign-cert-path', 'uses' => 'CertsController@sign']);
	Route::get('cert/remove/{certId}', ['as' => 'remove-cert-path', 'uses' => 'CertsController@destroy']);
	Route::get('cert/{certId}', ['as' => 'cert-crt-path', 'uses' => 'CertsController@getCert']);
	Route::get('cert/{certId}/download', ['as' => 'cert-crt-download-path', 'uses' => 'CertsController@downloadCert']);
	Route::get('cert/{certId}/key', ['as' => 'cert-key-path', 'uses' => 'CertsController@getKey']);
	Route::get('cert/{certId}/key/download', ['as' => 'cert-key-download-path', 'uses' => 'CertsController@downloadKey']);
	Route::get('logout', ['as' => 'auth-logout-path', 'uses' => 'AuthController@doLogout']);

	Route::group(['before' => 'isAdmin'], function() {
		Route::get('users', ['as' => 'users-path', 'uses' => 'UsersController@index']);
		Route::get('user/{userId}', ['uses' => 'UsersController@show']);
		Route::post('user', ['as' => 'create-user-path', 'uses' => 'UsersController@store']);
		Route::post('user/edit', ['as' => 'edit-user-path', 'uses' => 'UsersController@edit']);
		Route::get('user/remove/{userId}', ['as' => 'remove-user-path', 'uses' => 'UsersController@destroy']);

		Route::get('rootCA', ['as' => 'root-ca-path', 'uses' => 'CertsController@rootCAIndex']);
		Route::post('rootCA', ['as' => 'create-root-ca-path', 'uses' => 'CertsController@rootCACreate']);
		Route::get('rootCA/download/cert', ['as' => 'download-public-root-ca-cert-path', 'uses' => 'CertsController@rootCADownloadCert']);
		Route::get('rootCA/download/key', ['as' => 'download-public-root-ca-key-path', 'uses' => 'CertsController@rootCADownloadKey']);
		Route::get('rootCA/remove', ['as' => 'remove-root-ca-path', 'uses' => 'CertsController@rootCARemove']);
	});
});
