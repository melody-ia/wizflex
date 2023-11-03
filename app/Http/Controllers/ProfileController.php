<?php

namespace App\Http\Controllers;

use App\Models\EndAuction;
use App\Models\Nft;
use App\Models\NftLikeCun;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


use App\Models\Profile;
use App\Models\CollectionLike;
use App\Models\CollectionLikeCun;

use App\Models\ApplyAuthAuthor;
use App\Models\LogApplyAuthAuthor;

use App\Http\Controllers\NftController;


class ProfileController extends Controller
{
	public function hasCollectionLike($address,$to_address){
		$like = CollectionLike::where('address',$address)->where('to_address',$to_address)->first();
		if(empty($like) == true){
			return false;
		}

		return true;
	}

	public function collectionLikeCount($address){
		$like = CollectionLikeCun::find($address);
		if(empty($like) == true){
			return 0;
		}

		return $like->cun;
	}

	public function getCollection(Request $request,$address){
		$profile = Profile::where('address',$address)->first();

		$response = [];
		if(empty($profile) == true){
			$response = [
				'auth' => 0,
				'address' => $address,
				'name' => $address,
				'nick' => $address,
				'description' => "소개 내용이 없습니다",
				'like_count' => 0,
				'like' => $this->hasCollectionLike($request->my_address,$address)
			];
		}else{
			$response = [
				'auth' => $profile->auth,
				'address' => $profile->address,
				'name' => $profile->name,
				'nick' => $profile->nick,
				'description' => $profile->description,
				'website' => $profile->website_url,
				'twitter_url' => $profile->twitter_url,
				'blog_url' => $profile->blog_url,
				'instagram_url' => $profile->instagram_url,
				'like_count' => $profile->like_cun,
				'like' => $this->hasCollectionLike($request->my_address,$address)
			];
		}

		if(empty($profile->name) == true){
			$response['name'] = $address;
		}

		if(empty($profile->nick) == true){
			$response['nick'] = $address;
		}

		if(empty($profile->description) == true){
			$response['description'] = "소개 내용이 없습니다";
		}

		if(empty($profile) == true || empty($profile->avatar_image) == true){
			$response['avatar_image'] = envDB('BASE_IMAGE_URI') . '/img/profile.png';
		}else{
			$response['avatar_image'] = $profile->avatar();
		}

		if(empty($profile) == true || empty($profile->cover_image) == true){

		}else{
			$response['cover_image'] = $profile->cover();
		}

        if (empty($request->app) == false ){
            $response['info'] = $this->AppCollectionInfo($profile, $address);
        }

		return response()->json($response);
	}



	public function hotCollection(Request $request){
        $limit = empty($request->limit) ? 3 : $request->limit;
        if(empty($request->minNFT) == true){
            $minNFT = 0;
        }else{
            $minNFT = $request->minNFT;
        }

		$data = Profile::where('create_cun','>=',$minNFT)->orderBy('like_cun','desc')->offset(0)->limit($limit)->get();

		$profiles = [];
		foreach($data as $profile){
			$data = [
				'auth' => $profile->auth,
				'address' => $profile->address,
				'name' => $profile->name,
				'description' => $profile->description,
			];

			if(empty($profile->name) == true){
				$data['name'] = $profile->address;
			}

			if(empty($profile->nick) == true){
				$data['nick'] = $profile->address;
			}

			if(empty($profile->description) == true){
				$data['description'] = '소개 내용이 없습니다';
			}

			if(empty($profile) == true || empty($profile->avatar_image) == true){
				$data['avatar_image'] = envDB('BASE_IMAGE_URI') . '/img/profile.png';
			}else{
				$data['avatar_image'] = $profile->avatar();
			}

			if(empty($profile) == true || empty($profile->cover_image) == true){

			}else{
				$data['cover_image'] = $profile->cover();
			}

			$data['like_count'] = $profile->like_cun;

            if (empty($request->my_address) != true){
                $data['isLiked'] = CollectionLike::where('address',  $request->my_address)->where('to_address',$profile->address)->exists();
            }else {
                $data['isLiked'] = false;
            }
            //


            array_push($profiles,$data);
		}
		return response()->json($profiles);
	}

	public function explorer(Request $request,$tab){
		if(empty($request->offset) == true){
			$offset = 0;
		}else{
			$offset = $request->offset;
		}

        if(empty($request->order_by) == true){
            $order_by = 'created_at';
        }else{
            $order_by = $request->order_by;
        }

        if(empty($request->minNFT) == true){
            $minNFT = 0;
        }else{
            $minNFT = $request->minNFT;
        }

        $where = [];
		if($tab == 'all'){
			$profile = new Profile();
            $profile = $profile->where('create_cun','>=',$minNFT);
		}else if($tab == 'auth'){
			$profile = Profile::where('auth',1)->where('create_cun','>=',$minNFT);
		}else if($tab == 'unauth'){
			$profile = Profile::where('auth',0)->where('create_cun','>=',$minNFT);
		}

		if(empty($request->q) == false){
            // Str::contains('String', ['val1', 'val2']) // 해당 문자가 포함되는지 체크
            // $matches = Str::is('0x*', $address'); // true //
			if(strlen($request->q) == 42 && substr(Str::of($request->q)->trim(),0,2) == '0x'){
                $where[] = ['address', 'like', "%{$request->q}%"];
//                $where[] = ['create_cun','>=',$minNFT];
//				$profile = $profile->where('address', 'like',"%{$request->q}%")->where('create_cun','>=',$minNFT);
			}else{
                $where[] = ['name','like',"%{$request->q}%"];
                $where[] = ['create_cun','>=',$minNFT];
//				$profile = $profile->where('name','like',"%{$request->q}%")->where('create_cun','>=',$minNFT);
			}
		}

        $profile = $profile->where($where);

		$total = $profile->count();

		$profile = $profile->offset($offset)->limit(12);
		$profile = $profile->orderBy($order_by,'desc');


		$data = $profile->get();

		$profiles = [];
		foreach($data as $profile){
			$data = [
				'auth' => $profile->auth,
				'address' => $profile->address,
				'name' => $profile->name,
				'nick' => $profile->nick,
				'description' => $profile->description
			];

			if(empty($profile->name) == true){
				$data['name'] = $profile->address;
			}

			if(empty($profile->nick) == true){
				$data['nick'] = $profile->address;
			}

			if(empty($profile->description) == true){
				$data['description'] = "소개 내용이 없습니다";
			}

			if(empty($profile) == true || empty($profile->avatar_image) == true){
				$data['avatar_image'] = envDB('BASE_IMAGE_URI') . '/img/profile.png';
			}else{
				$data['avatar_image'] = $profile->avatar();
			}

			if(empty($profile) == true || empty($profile->cover_image) == true){

			}else{
				$data['cover_image'] = $profile->cover();
			}

			array_push($profiles,$data);
		}
		return response()->json([
			'total' => $total,
			'data'  => $profiles
		]);
	}

	public function urlVerify($url){
		if (!filter_Var($url,FILTER_VALIDATE_URL)){
		  return false;
		}

		return true;
	}

	public function updateProfile(Request $request){
		$profile = Profile::where("address",$request->auth_address)->first();
		if(empty($profile) == true){
			$profile = new Profile();
			$profile->address = strtolower($request->auth_address);
			$profile->avatar_image = '';
			$profile->cover_image = '';
		}

		if(empty($request->name) == true || mb_strlen($request->name,'utf8') < 1 || mb_strlen($request->name,'utf8') > 50){
			return response()->json([
				"error" => [
					"message" => "이름은 최소 1글자 이상 최대 50글자 까지 허용됩니다"
				]
			],200);
		}else if(empty($request->nick) == true || mb_strlen($request->nick,'utf8') < 1 || mb_strlen($request->nick,'utf8') > 50){
			return response()->json([
				"error" => [
					"message" => "닉네임은 최소 1글자 이상 최대 50글자 까지 허용됩니다"
				]
			],200);
		}else if(empty($request->description) == true || mb_strlen($request->description,'utf8') < 20 || mb_strlen($request->description,'utf8') > 200){
			return response()->json([
				"error" => [
					"message" => "컬렉션 설명은 최소 20글자 이상 최대 200글자 까지 허용됩니다"
				]
			],200);
		}else if(empty($request->website_url) == false && ($this->urlVerify($request->website_url) == false || strlen($request->website_url) > 250) ){
			return response()->json([
				"error" => [
					"message" => "웹사이트 URL 이 잘못되었거나 250자를 초과하였습니다 http:// 또는 https:// 를 안붙여주신경우 붙여주시길 바랍니다"
				]
			],200);
		}else if(empty($request->blog_url) == false && ($this->urlVerify($request->blog_url) == false || strlen($request->blog_url) > 250) ){
			return response()->json([
				"error" => [
					"message" => "블로그 URL 이 잘못되었거나 250자를 초과하였습니다 http:// 또는 https:// 를 안붙여주신경우 붙여주시길 바랍니다"
				]
			],200);
		}else if(empty($request->twitter_url) == false && ($this->urlVerify($request->twitter_url) == false || strlen($request->twitter_url) > 250) ){
			return response()->json([
				"error" => [
					"message" => "트위터 URL 이 잘못되었거나 250자를 초과하였습니다 http:// 또는 https:// 를 안붙여주신경우 붙여주시길 바랍니다"
				]
			],200);
		}else if(empty($request->instagram_url) == false && ($this->urlVerify($request->instagram_url) == false || strlen($request->instagram_url) > 250) ){
			return response()->json([
				"error" => [
					"message" => "인스타 URL 이 잘못되었거나 250자를 초과하였습니다 http:// 또는 https:// 를 안붙여주신경우 붙여주시길 바랍니다"
				]
			],200);
		}

		if(empty($request->website_url) == false){
			$profile->website_url = $request->website_url;
		}

		if(empty($request->blog_url) == false){
			$profile->blog_url = $request->blog_url;
		}

		if(empty($request->twitter_url) == false){
			$profile->twitter_url = $request->twitter_url;
		}

		if(empty($request->instagram_url) == false){
			$profile->instagram_url = $request->instagram_url;
		}

		$profile->name = $request->name;
		$profile->nick = $request->nick;
		$profile->description = $request->description;
		$profile->save();

		return response()->json([
			'updated' => true
		],200);
	}


	public function topSeller(Request $request){
        $limit = empty($request->limit) ? 8 : $request->limit;
        $where = [];

        if (empty($request->minNFT) === false){
            $where[] = ['create_cun','>=',$request->minNFT];
        }

		$sellers = Profile::where($where)->orderBy("month_sales_amount","desc")->limit($limit)->get();

		$results = [];
		foreach($sellers as $seller){
			$data = [
				'auth' => $seller->auth,
				'address' => $seller->address,
				'name' => $seller->name,
				'nick' => $seller->nick,
				'description' => $seller->description,
				'prev_month_sales_amount' => 0,
				'month_sales_amount' => $seller->month_sales_amount,
			];

			if(empty($seller->name) == true){
				$data['name'] = $seller->address;
			}

			if(empty($seller->nick) == true){
				$data['nick'] = $seller->address;
			}

			if(empty($seller->description) == true){
				$data['description'] = "소개 내용이 없습니다";
			}

			if(empty($seller) == true || empty($seller->avatar_image) == true){
				$data['avatar_image'] = envDB('BASE_IMAGE_URI') . '/img/profile.png';
//				$data['avatar_image'] = envDB('BASE_IMAGE_URI') . '/img/profile.svg';
			}else{
				$data['avatar_image'] = $seller->avatar();
			}

			if(empty($seller) == true || empty($seller->cover_image) == true){

			}else{
				$data['cover_image'] = $seller->cover();
			}


			array_push($results,$data);
		}

		return response()->json([
			"data" => $results
		]);
	}

	public function updateAvatar(Request $request){
		$extension = strtolower($request->avatar->extension());
		if($extension != 'jpg' && $extension != 'png'){
			return response()->json([
				'error' => [
					'message' => "허가된 파일 확장자가 아닙니다"
				]
			]);
		}

		$UPLOAD_SIZE_PROFILE = envDB('UPLOAD_SIZE_PROFILE');
		if($request->file('avatar')->getSize() > $UPLOAD_SIZE_PROFILE){
			if($UPLOAD_SIZE_PROFILE >= 1000000){
				$size = round($UPLOAD_SIZE_PROFILE / 1000000,1);
				$size .= 'MB';
			}else{
				$size = $UPLOAD_SIZE_PROFILE / 1000;
				$size .= 'KB';
			}

			return response()->json([
				'error' => [
					'message' => "{$size} 이하의 파일만 업로드하실수 있습니다"
				]
			]);
		}

		$filename = Str::random(30) . "." . $extension;


		if(empty(envDB('IS_AWS_S3')) == true){
			$path = $request->avatar->storeAs('profile',$filename,'public');
			if($path == false){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
			}
		}else{

            $path = $request->avatar->path();
            $result = $this->saveResizeFile($request->file("avatar"), 165, "avatar-files");
            if (empty($result) == true){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
            }

			$filename = $result;


		}

		$profile = Profile::where("address",$request->auth_address)->first();
		if(empty($profile) == true){
			$profile = new Profile();
			$profile->address = $request->auth_address;
		}

		$profile->avatar_image = $filename;
		$profile->save();


		return response()->json([
			'avatar_image' => $profile->avatar(),
			'updated' => true
		],200);
	}
	public function updateCover(Request $request){
		$extension = strtolower($request->cover->extension());
		if($extension != 'jpg' && $extension != 'png'){
			return response()->json([
				'error' => [
					'message' => "허가된 파일 확장자가 아닙니다"
				]
			]);
		}

		$UPLOAD_SIZE_COVER = envDB('UPLOAD_SIZE_COVER');
		if($request->file('cover')->getSize() > $UPLOAD_SIZE_COVER){
			if($UPLOAD_SIZE_COVER >= 1000000){
				$size = round($UPLOAD_SIZE_COVER / 1000000,1);
				$size .= 'MB';
			}else{
				$size = $UPLOAD_SIZE_COVER / 1000;
				$size .= 'KB';
			}

			return response()->json([
				'error' => [
					'message' => "{$size} 이하의 파일만 업로드하실수 있습니다"
				]
			]);
		}

		$filename = Str::random(30) . "." . $extension;
		if(empty(envDB('IS_AWS_S3')) == true){
			$path = $request->cover->storeAs('profile',$filename,'public');
			if($path == false){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
			}
		}else{
			$path = $request->cover->path();
			if(Storage::disk('s3')->put('/cover-files/'.$filename,file_get_contents($path),'public') == false){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
			}
		}

		$profile = Profile::where("address",$request->auth_address)->first();
		if(empty($profile) == true){
			$profile = new Profile();
			$profile->address = $request->auth_address;
		}

		$profile->cover_image = $filename;
		$profile->save();

		return response()->json([
			'cover_image' => $profile->cover(),
			'updated' => true
		],200);
	}

	public function applyAuth(Request $request){
		if(empty($request->name) == true){
			return response()->json([
				'error' => [
					'message' => "이름이 누락되었 습니다"
				]
			]);
		}else if(empty($request->phone) == true){
			return response()->json([
				'error' => [
					'message' => "연락처가 누락되었 습니다"
				]
			]);
		}else if(empty($request->email) == true){
			return response()->json([
				'error' => [
					'message' => "이메일이 누락되었 습니다"
				]
			]);
		}else if(empty($request->description) == true){
			return response()->json([
				'error' => [
					'message' => "소개가 누락되었 습니다"
				]
			]);
		}

		$applyAuthAuthor = ApplyAuthAuthor::where('address',$request->auth_address)->first();
		if(empty($applyAuthAuthor) == false){
			return response()->json([
				'error' => [
					'message' => "이미 신청이 접수되었 습니다"
				]
			]);
		}

		$applyAuthAuthor = new ApplyAuthAuthor();
		$applyAuthAuthor->address = $request->auth_address;
		$applyAuthAuthor->name = $request->name;
		$applyAuthAuthor->phon = $request->phone;
		$applyAuthAuthor->email = $request->email;
		$applyAuthAuthor->description = $request->description;
		$applyAuthAuthor->save();

		return response()->json([
			'data' => true
		]);
	}

    /**
     * @param Request $req
     * @param $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferHistory(Request $req, $address){
        $order_key  = empty($request->order_key)?'created_at':$request->order_key;
        $order_sort = empty($request->order_sort)?'desc':$request->order_sort;
        $offset = empty($request->offset)?0:$request->offset;
        $limit = empty($request->limit)?10:$request->limit;

        $purchase_empty = Purchase::join('transfer', 'purchase.tx_hash', '=', 'transfer.tx_hash')
            ->where('transfer.from', '=', $address)
            ->orWhere('transfer.to', '=', $address)->exists();
        $purchase_data = [];

        if ($purchase_empty){
//            $purchases = Purchase::orderBy($order_key,$order_sort)->get();
            $purchases = Purchase::orderBy($order_key,$order_sort)->offset($offset)->limit($limit)->get();
            foreach($purchases as $purchaseData){
                $nft = Nft::find($purchaseData->nft_id);
                $nft->file_url = $nft->file();
                $nft->transfer =  $purchaseData->transfer();
                $nft->sortation = "purchase";
                array_push($purchase_data,$nft);
            }
            unset($nft);
            $purchase_total = Purchase::count();
        }


        $endAuctions_empty = EndAuction::join('transfer', 'end_auction.tx_hash', '=', 'transfer.tx_hash')
            ->where('transfer.from', '=', $address)
            ->orWhere('transfer.to', '=', $address)->exists();
        $auctions_data = [];

        if ($endAuctions_empty){
            $endAuctions = EndAuction::orderBy($order_key,$order_sort)->offset($offset)->limit($limit)->get();
            foreach($endAuctions as $endAuction){
                $nft = Nft::find($endAuction->nft_id);
                $nft->file_url = $nft->file(); // ????
                $nft->transfer =  $endAuction->transfer();
                $nft->sortation = 'auction';
                array_push($auctions_data,$nft);
            }
            $auctions_total = EndAuction::count();
        }

        $data = array_merge($purchase_data, $auctions_data);
        if (empty($data) != true){
            foreach($data as $key => $val){
                if ($val->transfer->to == $address ||  $val->transfer->from == $address) continue;
                unset($data[$key]);
            }

            usort($data, function ($a, $b) {
                unset($a->creator_ip);
                unset($b->creator_ip);
                return strcmp($b->created_at, $a->created_at);
            });
        }

        return response()->json([
//            'totalAmount' => [
//                'purchase_totalAmount' => $purchase_totalAmount,
//                'auctions_totalAmount' => $auctions_totalAmount
//            ],
            'total' => empty($data) ? 0 : $purchase_total + $auctions_total,
            'data' => $data
        ]);
    }


    public function AppCollectionInfo($profile, $address){
        $itmes = Nft::where('creator_address',$address)->count();
        $nfts = Nft::where('creator_address', $address)->get();
        $like_count = 0;
        foreach ($nfts as $nft){
            $like = NftLikeCun::where('token_id', $nft->id);
            if($like->exists() == false) continue;
            $like_count += $like->first()->cun;
        }

        $minted = Nft::where('creator_address',$address)->whereNotNull('token_id')->count();
        return [
            'items' => $itmes,
            "likes" => $like_count,
            "sales" => empty($profile->month_sales_amount) ? 0 : $profile->month_sales_amount,
            "minted" => $minted,
        ];
    }
}

