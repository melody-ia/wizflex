<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*
	JSON API V1
*/
Route::prefix('v1')->group(function() {

	//NFT 레이어 APIs
	Route::prefix('layer')->group(function() {
		//레이어 벨류 추가
		Route::get('value','App\Http\Controllers\LayerController@GetLayerValues');

		//레이어 미리보기
		Route::post('gen-image','App\Http\Controllers\LayerController@GenImage');
	});

	/*
		컬렉션 APIs
	*/
	Route::prefix('collection')->group(function() {

		Route::get('top-seller','App\Http\Controllers\ProfileController@topSeller');

		//인기 컬렉션
		Route::get('hot','App\Http\Controllers\ProfileController@hotCollection');

		//컬렉션 찾기
		Route::get('explorer/{tab}','App\Http\Controllers\ProfileController@explorer');

		//컬렉션 정보
		Route::get('{address}','App\Http\Controllers\ProfileController@getCollection');

		//보유하고있는토큰
		Route::get('{address}/holding','App\Http\Controllers\NftController@holdingTokens');

		//생성한토큰목록
		Route::get('{address}/created','App\Http\Controllers\NftController@createdTokens');

        //거래된 내역 (신규 22.10.06)
        // {address}/Transfers
        Route::get('{address}/transfers','App\Http\Controllers\ProfileController@transferHistory');

		//판매등록한토큰목록
		Route::get('{address}/sale','App\Http\Controllers\NftController@saleTokens');

		Route::middleware(['auth.wallet'])->group(function () {
			Route::post('update-profile','App\Http\Controllers\ProfileController@updateProfile');
			Route::post('update-avatar','App\Http\Controllers\ProfileController@updateAvatar');
			Route::post('update-cover','App\Http\Controllers\ProfileController@updateCover');

			Route::post('apply-auth','App\Http\Controllers\ProfileController@applyAuth');
		});
	});


	/*
		NFT APIs
	*/
	Route::prefix('nft')->group(function () {

		//토큰탐색 (카테고리별로 목록 가져오기)
		Route::get('explorer/{tab}','App\Http\Controllers\NftController@explorer');

		//마감경매 목록 가져오기
		Route::get('auctionending','App\Http\Controllers\NftController@auctionEnding');

		//신규토큰 목록 가져오기
		Route::get('newtokens','App\Http\Controllers\NftController@newTokens');

		//NFT 정보 가져오기
		Route::get('{nft_id}','App\Http\Controllers\NftController@getNFT');

		//NFT 소유자 목록 가져오기
		Route::get('{nft_id}/owners','App\Http\Controllers\NftController@GetOwners');

        //NFT 거래 목록 가져오기
        Route::get('{nft_id}/transfers','App\Http\Controllers\NftController@getTransfersByOffset');

        //NFT 입찰 목록 가져오기
        Route::get('{nft_id}/bids','App\Http\Controllers\NftController@getBidsByOffset');

		//토큰검증 (토큰 발행 검증)
		Route::post('{nft_id}/txverify','App\Http\Controllers\NftController@txVerify');

		Route::middleware(['auth.wallet'])->group(function () {
			Route::post('/created','App\Http\Controllers\NftController@created');
			Route::post('/deleted','App\Http\Controllers\NftController@deleted');
			Route::post('/test/created','App\Http\Controllers\NftController@created_test');
		});
	});

	Route::prefix('erc-20')->group(function () {
		Route::get('{contractAddress}/allow','App\Http\Controllers\TokenController@getAllowance');

		Route::get('{contractAddress}/balance','App\Http\Controllers\TokenController@getBalance');
	});

	Route::middleware(['auth.wallet'])->group(function () {
		Route::post('like/{category}','App\Http\Controllers\LikeController@liked');
	});

    Route::prefix('like')->group(function () {
        Route::get('{address}/collection','App\Http\Controllers\NftController@getLikedCollection');
        Route::get('{address}/nft','App\Http\Controllers\NftController@getLikedNft');
    });

    Route::prefix('story')->group(function () {
        // story 팔로우 기준 정보 가져가기
        Route::get('{address}','App\Http\Controllers\StoryController@stories');
        // history 기준 정보 가져오기
        Route::get('{address}/history','App\Http\Controllers\StoryController@storiesHistory');
        // check story
        Route::post('/seen', 'App\Http\Controllers\StoryController@checkSeen');

//        Route::middleware(['auth.wallet'])->group(function () {
            Route::post('/create', 'App\Http\Controllers\StoryController@create');
            Route::post('/delete', 'App\Http\Controllers\StoryController@delete');
//        });
    });

	/*
	  스마트 계약 함수 hex로 인코딩 API
	*/
	Route::post('/solc/encodefunction','App\Http\Controllers\SolcController@encodefunction');

	/*

	*/
	Route::get('/solc/paused','App\Http\Controllers\SolcController@paused');

	/*
	  문의접수 API
	*/
	Route::post('/contact','App\Http\Controllers\ContactController@created');

    //문의 목록 (삭제해야됨)
    Route::get('/contact/dev-test/13245','App\Http\Controllers\DashController@contacts');
    //인증된 저자 목록
    Route::get('/authors/auth/dev-test/13245','App\Http\Controllers\DashController@authAuthors');
    //인증된 신청 저자 목록
    Route::get('/authors/auth/apply/dev-test/13245','App\Http\Controllers\DashController@applyAuthAuthors');






	/*
	  결제가 가능한 토큰 목록
	*/
	Route::get('/pay-tokens','App\Http\Controllers\PayTokenController@list');

    // Klip지갑 관련
    Route::prefix('klip')->group(function () {
        // klip지갑 잔액 로드
        Route::get('/{address}/balance','App\Http\Controllers\KlipController@klipBalance');

        // klip contract_exec 코드
        Route::post('/contract-exec','App\Http\Controllers\KlipController@contractExec');


//        Route::post('/testImage', 'App\Http\Controllers\KlipController@convertImage');
    });

	/*
		컨트렉트별 수수료 목록
	*/
	Route::get('/fees','App\Http\Controllers\DashController@Fees');

    Route::get('/wallet/{address}/balance','App\Http\Controllers\WalletController@getBalance');
});


/*
	거래소 APIs
*/
Route::prefix('exchange')->group(function () {

	//거래소 시세
	Route::get('price','App\Http\Controllers\ExchangeController@getPrice');

});

Route::get('/market/app/front_env', function () {
    return frontEnvJson();
});



Route::get('metadata/{nft_id}','App\Http\Controllers\MetaDataController@get');

/*
  스마트 계약 트랜잭션 이벤트 콜백
*/
Route::post('/callback','App\Http\Controllers\CallbackController@check');




