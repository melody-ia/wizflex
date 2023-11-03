<?php

use Illuminate\Support\Facades\Route;

/*
	TITLE 변경시 Vue.JS 도 변경 하시길 바랍니다
*/

Route::any('/', 'App\Http\Controllers\VueController@main');

Route::any('/explorer/{tab}', 'App\Http\Controllers\VueController@explorer');

Route::any('/authors/{tab}', 'App\Http\Controllers\VueController@authors');

Route::any('/nft/{nft_id}', 'App\Http\Controllers\VueController@nft');

Route::any('/profile/{address}/nfts/{tab}', 'App\Http\Controllers\VueController@collectionNfts');

Route::any('/profile/{address}/create', 'App\Http\Controllers\VueController@createNft');

Route::any('/profile/{address}/setting', 'App\Http\Controllers\VueController@setting');

Route::any('/contacts', 'App\Http\Controllers\VueController@contacts');

Route::any('/help', 'App\Http\Controllers\VueController@help');

Route::any('/service', 'App\Http\Controllers\VueController@service');

Route::any('/privacy', 'App\Http\Controllers\VueController@privacy');



Route::any('/admin-logout', 'App\Http\Controllers\AdminController@webLogout');

Route::any('/mail-test', 'App\Http\Controllers\MailController@test');
