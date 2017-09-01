<?php

Route::group(['namespace' => 'AtlassianConnectCore\Http\Controllers'], function () {
    Route::get('atlassian-connect.json', 'TenantController@descriptor')->name('descriptor');

    Route::post('installed', 'TenantController@installed')->name('installed');
    Route::post('disabled', 'TenantController@disabled')->name('disabled');

    Route::group(['middleware' => 'jwt'], function () {
        Route::post('enabled', 'TenantController@enabled')->name('enabled');
        Route::post('uninstalled', 'TenantController@uninstalled')->name('uninstalled');

        Route::get('hello', 'SampleController@index')->name('hello');
    });
});