<?php

namespace App\Http\Controllers;

use App\Models\CollectionLike;
use App\Models\Profile;
use App\Models\Stories;
use App\Models\StoriesSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use DateInterval;
use DateTime;
use Illuminate\Support\Carbon;
use BlockSDK;



class StoryController extends Controller
{

    public function netClient(){
        if(envDB('BASE_MAINNET') == 'ETH')
            return BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
        if(envDB('BASE_MAINNET') == 'BSC')
            return BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
        if(envDB('BASE_MAINNET') == 'KLAY')
            return BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
    }

    public function create(Request  $req){

        $rule = [
            'user_address' => ['required', 'size:42'], // == 'wallet Address' => 'required'
//            'auth_address' => ['required', 'size:42'], // == 'wallet Address' => 'required'
            'story_image' => ['required', 'mimes:jpeg,png,jpg,gif', 'max:10240'], // == 'image' => 'required|min:10'
            'swipeText' => ['max:30'], // == 'content Text'
        ];
        $validator = Validator::make($req->all(), $rule );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if(empty($req->story_image) == false && $req->story_image != null && $req->story_image != 'null'){
            $extension = strtolower($req->story_image->getClientOriginalExtension());
        }else{
            $extension = "png";
        }

        $filename = Str::random(30) . "." . $extension;
        $result = Storage::disk('s3')->put("/stories/" . $filename,file_get_contents($req->file('story_image')->getRealPath()),'public');
        if (!$result) response()->json([
           'error' => 'File upload failed.'
        ], 400);

        $story = new Stories();
        $story->address = $req->user_address;
//        $story->address = strtolower($req->auth_address);
        $story->story_image = $filename;
        $story->swipeText = empty($req->swipeText) ? null : $req->swipeText;
        $story->save();

        return response()->json([
            'data' => $story,
            'state' => true
        ]);
    }

    public function stories(Request $req, $address){

        $offset = empty($req->offset) ? 0 : $req->offset;
        $limit = empty($req->limit) ? 10 : $req->limit;
        $like_list = CollectionLike::where('address',Str::lower($address))->offset($offset)->limit($limit)->get();
        $now = new DateTime();
        $sub = $now->sub(new DateInterval('PT168H'));

        if(empty($like_list) === true){
            return response()->json([
                'error' => [
                    'message' => "좋아요를 표시한 Collection이 존재하지 않습니다."
                ]
            ]);
        }

        $res = [];
        foreach ($like_list as $key => $val){
            if ($val->to_address == $address) continue;
            $stories = Stories::where('address', $val->to_address)->whereDate('created_at', '>=', $sub);
            if ($stories->exists() === false) {
                continue;
            }

            $profile = Profile::where('address', $val->to_address)->first();
            $temp = [];

            $stories = $stories->get();
            $temp['user_name'] = $profile->name;
            $temp['user_address'] = $profile->address;
            $temp['user_image'] = $profile->avatar();
            $temp['seen'] = true;
            $temp['date'] = $stories[0]['created_at'];

            foreach ($stories as $k => $v){
                $arr = $where = [];
                $where[] = ['story_id', $v->story_id];
                $where[] = ['address', $address];

                $seen = StoriesSeen::where($where)->first();

                if (empty($seen) || empty($seen) !== true && $seen['seen'] === false) {
                    $temp['seen'] = false;
                }
//                return $seen;
                $arr['story_id'] = $v->story_id;
                $arr['user_address'] = $v->address;
                $arr['story_image'] = envDB('BASE_AWS_S3_URI') . '/stories/' . $v->story_image;
                $arr['swipeText'] = $v->swipeText;
                $arr['seen'] = empty($seen) ? false : $seen['seen'];
                $arr['updated_at'] = $v->updated_at;
                $arr['created_at'] = $v->created_at;

                $temp['stories'][] = $arr;
            }

            if($temp['seen'] !== true){
                $res[] = $temp;
            }

        }

        $total = CollectionLike::join('stories', 'stories.address', '=', 'collection_like.to_address')
            ->where('collection_like.address', '=', $address)->where('stories.address' ,'!=',$address)->whereDate('stories.created_at', '>=', $sub)->count();

        return response()->json([
            'data' => $res,
            'total' => $total,
        ]);
    }

    public function storiesHistory(Request $req, $address){
        $offset = empty($req->offset) ? 0 : $req->offset;
        $limit = empty($req->limit) ? 10 : $req->limit;

        // 내가 만든 것만. 페이징 처리 필요
        $story_list = Stories::where('address', $address)->offset($offset)->limit($limit)->distinct();
//        $stories = Stories::where('address', $address)->paginate($limit);

        $result = [];

        foreach ($story_list->get('created_at') as $key => $val){
            $arr = [];

            $from = new DateTime($val->getDateYmd());
            $to = $from->add(new DateInterval('PT24H'));
            $from = $val->getDateYmd();

            $story = Stories::where('address', $address)->whereBetween('created_at', [$from, $to])->get();
            if (empty($story) === true) continue;

            $profile = Profile::where('address', $address)->first();
            $arr['user_name'] = $profile->name;
            $arr['user_address'] = $profile->address;
            $arr['user_image'] = $profile->avatar();
            $arr['seen'] = true;
            $arr['date'] = $from;
//
            foreach ($story as $k => $v){
                $stories = $where =  [];

                $where[] = ['story_id', $v->story_id];
                $where[] = ['address', $address];

                $seen = StoriesSeen::where($where)->first();
                if (empty($seen) || empty($seen) !== true && $seen['seen'] === false) {
                    $arr['seen'] = false;
                }

                $stories['story_id'] = $v->story_id;
                $stories['user_address'] = $v->address;
                $stories['story_image'] = envDB('BASE_AWS_S3_URI') . '/stories/'.$v->story_image;
                $stories['swipeText'] = $v->swipeText;
                $stories['seen'] = empty($seen) ? false : $seen['seen'];
                $stories['updated_at'] = $v->updated_at;
                $stories['created_at'] = $v->updated_at;
                $arr['stories'][] = $stories;
            }



            $result[] = $arr;
        }


//        $stories->map(function ($row) use ($stories, $address) {
//            foreach (array_filter($stories->data, function($v) use ($row) {
//                return $v['created_at'];
//            }) as $item) {
//                $result[] =
//            }
//            $profile = Profile::where('address', $row['address'])->first();
//            $arr = [];
//
//            $arr['user_name'] = $profile->name;
//            $arr['user_address'] = $profile->address;
//            $arr['user_image'] = $profile->avatar();
//            $arr['seen'] = false;
//            $arr['date'] = $row['created_at'];
//
//            $where[] = ['story_id', $row['story_id']];
//            $where[] = ['address', $address];
//            $seen = StoriesSeen::where($where)->first();
//            $row['seen'] = empty($seen) ? false : $seen['seen'];
//            return $row;
//        });

        return response()->json([
            'data' => $result,
            'total' => $story_list->count()
        ]);
    }


    public function delete(Request  $req){

    }

    public function checkSeen(Request $req){
        $rule = [
            'auth_address' => ['required', 'size:42'], // == 'wallet Address' => 'required'
            'story_id' => ['required'], // == 'story_id PK' => 'required|min:10'
        ];

        $validator = Validator::make($req->all(), $rule );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $matchThese = ['story_id'=>$req->story_id,'address'=> $req->auth_address];
        $seen = StoriesSeen::updateOrCreate($matchThese,['seen'=>1]);

        return response()->json([
//            'data' => $req->all(),
            'data' => $seen,
            'state' => true
        ]);
    }

}
