<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NftLike;
use App\Models\CollectionLike;

use App\Models\Nft;
use App\Models\Profile;

class LikeController extends Controller
{
    public function nftLike($from_address,$token_id){
        if(empty($token_id) == true){
            return response()->json([
                'error' => [
                    'message' => "발행되지 않은 NFT는 좋아요를 할수 없습니다"
                ]
            ]);
        }

        $like = NftLike::where('address',$from_address)->where('token_id',$token_id)->first();
        if(empty($like) == false){
            return $this->nftUnLike($from_address,$token_id);
        }

        $nft = Nft::where('id',$token_id)->first();

        if(empty($nft)== true){
            return response()->json([
                'error' => [
                    'message' => "존재하지 않는 NFT 입니다"
                ]
            ]);
        }

        $like = new NftLike();
        $like->address = $from_address;
        $like->token_id = $token_id;
        $like->save();

        return true;
    }

    public function nftUnLike($from_address,$token_id){
        $like = NftLike::where('address',$from_address)->where('token_id',$token_id)->first();
        if(empty($like) == true){
            return response()->json([
                'error' => [
                    'message' => "존재하지 않는 좋아요 입니다"
                ]
            ]);
        }

        $nft = Nft::where('id',$token_id)->first();
        if(empty($nft)== true){
            return response()->json([
                'error' => [
                    'message' => "존재하지 않는 NFT 입니다"
                ]
            ]);
        }

        $like->delete();

        return false;
    }
	public function collectionLike($from_address,$collection_address){
		$like = CollectionLike::where('address',$from_address)->where('to_address',$collection_address)->first();
		if(empty($like) == false){
			return $this->collectionUnLike($from_address,$collection_address);
		}

		$profile = Profile::where('address',$collection_address)->first();
		if(empty($profile)== true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 컬렉션 입니다"
				]
			]);
		}

		$like = new CollectionLike();
		$like->address = $from_address;
		$like->to_address = $collection_address;
		$like->save();

		return true;
	}

	public function collectionUnLike($from_address,$collection_address){
		$like = CollectionLike::where('address',$from_address)->where('to_address',$collection_address)->first();
		if(empty($like) == true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 좋아요 입니다"
				]
			]);
		}

		$profile = Profile::where('address',$collection_address)->first();
		if(empty($profile)== true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 컬렉션 입니다"
				]
			]);
		}

		$like->delete();

		return false;
	}

	public function liked(Request $request,$category){

		if($category != 'nft' && $category != 'collection'){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 카테고리 입니다"
				]
			]);
		}



		if($category == 'nft'){
			$result = $this->nftLike($request->auth_address,$request->token_id);
		}else if($category == 'collection'){
			$result = $this->collectionLike($request->auth_address,$request->collection_address);
		}

		if($result !== false && $result !== true){
			return $result;
		}

		return response()->json([
			'liked' => $result
		]);
	}



}
