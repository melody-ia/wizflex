<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*
	JSON API V1
*/

//관리자 로그인
Route::get('auth-check','App\Http\Controllers\AdminController@authCheck');

Route::post('login','App\Http\Controllers\AdminController@login');
Route::get('logout','App\Http\Controllers\AdminController@logout');

Route::middleware(['auth.admin'])->group(function () {
	Route::get('me','App\Http\Controllers\AdminController@me');
	
	//대시보드 통계 데이터
	Route::get('dash','App\Http\Controllers\DashController@get');
	
	//NFT 전체 목록
	Route::get('nfts','App\Http\Controllers\DashController@nfts');
	
	//NFT 검색
	Route::get('nfts/search','App\Http\Controllers\DashController@searchNfts');
	
	//NFT 삭제
	Route::post('nfts/delete','App\Http\Controllers\DashController@deleteNft');
	
	//경매 진행중인 NFT 전체 목록
	Route::get('nfts/auction','App\Http\Controllers\DashController@auctionNfts');
	
	//경매 성사
	Route::get('nfts/end-auction','App\Http\Controllers\DashController@endAuction');
	
	//경매 성사 검색
	Route::get('nfts/end-auction/search','App\Http\Controllers\DashController@searchEndAuction');
	
	//거래 성사
	Route::get('nfts/purchase','App\Http\Controllers\DashController@purchase');
	
	//거래 성사 검색
	Route::get('nfts/purchase/search','App\Http\Controllers\DashController@searchPurchase');
	
	//인증된 저자 목록
	Route::get('authors/auth','App\Http\Controllers\DashController@authAuthors');
	
	//인증된 저자 검색
	Route::get('authors/auth/search','App\Http\Controllers\DashController@searchAuthAuthors');
	
	//인증된 신청 저자 목록
	Route::get('authors/auth/apply','App\Http\Controllers\DashController@applyAuthAuthors');
	
	//인증된 수동 추가
	Route::post('authors/auth/add','App\Http\Controllers\DashController@addAuthAuthors');
	
	//인증된 신청 승인,거부
	Route::post('authors/auth/apply','App\Http\Controllers\DashController@processAuthAuthors');
	
	//인증 처리 로그
	Route::get('authors/auth/logs','App\Http\Controllers\DashController@authAuthorLogs');
	
	//인증 처리 로그 검색
	Route::get('authors/auth/logs/search','App\Http\Controllers\DashController@searchAuthAuthorLogs');
	
	//관리자 목록
	Route::get('users','App\Http\Controllers\DashController@users');
	
	//관리자 갱신
	Route::post('users/update','App\Http\Controllers\DashController@updateUser')->middleware(['auth.super']);
	
	//관리자 추가 
	Route::post('users/create','App\Http\Controllers\DashController@createUser')->middleware(['auth.super']);
	
	//관리자 제거
	Route::post('users/delete','App\Http\Controllers\DashController@deleteUser')->middleware(['auth.super']);
	
	
	//문의 목록
	Route::get('contacts','App\Http\Controllers\DashController@contacts');
	
	//문의 검색
	Route::get('contacts/search','App\Http\Controllers\DashController@searchContacts');
	
	//문의 답변
	Route::post('contacts/reply','App\Http\Controllers\DashController@contactsReply');
	
	
	//백엔드 설정 가져오기
	Route::get('setting/backend','App\Http\Controllers\SettingController@getBackend')->middleware(['auth.super']);
	
	//프론트 설정 가져오기
	Route::get('setting/front','App\Http\Controllers\SettingController@getFront')->middleware(['auth.super']);
	
	//NFT 설정 가져오기
	Route::get('setting/nft','App\Http\Controllers\SettingController@getNft')->middleware(['auth.super']);
	
	//백엔드 설정 업데이트
	Route::post('setting/backend/update','App\Http\Controllers\SettingController@setBackend')->middleware(['auth.super']);
	
	//프론트 설정 업데이트
	Route::post('setting/front/update','App\Http\Controllers\SettingController@setFront')->middleware(['auth.super']);
	
	//NFT 설정 업데이트
	Route::post('setting/nft/update','App\Http\Controllers\SettingController@setNft')->middleware(['auth.super']);
	
	//카테고리 삭제
	Route::post('setting/category/delete','App\Http\Controllers\SettingController@deleteCategory')->middleware(['auth.super']);
	
	//카테고리 추가
	Route::post('setting/category/add','App\Http\Controllers\SettingController@addCategory')->middleware(['auth.super']);
	
	//토큰 목록 가져오기
	Route::get('setting/token','App\Http\Controllers\SettingController@getToken')->middleware(['auth.super']);
	
	//토큰 추가
	Route::post('setting/token/add','App\Http\Controllers\SettingController@addToken')->middleware(['auth.super']);
	
	//토큰 삭제
	Route::post('setting/token/delete','App\Http\Controllers\SettingController@deleteToken')->middleware(['auth.super']);
	
	//레이어 타입 추가
	Route::post('layer/add-type','App\Http\Controllers\LayerController@AddType');
	
	//레이어 타입 삭제
	Route::post('layer/remove-type','App\Http\Controllers\LayerController@RemoveType');
	
	//레이어 타입들 가져오기
	Route::get('layer/type','App\Http\Controllers\LayerController@GetType');
		
	//레이어 벨류 추가
	Route::post('layer/add-value','App\Http\Controllers\LayerController@AddValue');
	
	//레이어 벨류 제거
	Route::post('layer/remove-value','App\Http\Controllers\LayerController@RemoveValue');
});
?>
