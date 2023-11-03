<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Models\User;

use App\Models\LaravelEnv;
use App\Models\FrontEnv;
use App\Models\CategoryEnv;
use App\Models\PayToken;

use BlockSDK;

if(envDB('HAS_MULTI_NET') == 1){
    //다중 메인넷
    class NetSettingController extends Controller {
        use Modules\MultiNetSettingController;
    }
}else{
    //싱글 메인넷
    class NetSettingController extends Controller {
        use Modules\SingleNetSettingController;
    }
}

class SettingController extends NetSettingController
{
    public function setBackendEnv($name,$value){
        $env = LaravelEnv::firstOrCreate(
            [
                'name' => $name
            ],
            [
                'value' => $value
            ]
        );
        $env->value = $value;
        $env->save();

        return $env;
    }
    public function getBackendEnv($name){
        $env = LaravelEnv::find($name);
        if(empty($env) == true){
            return "";
        }

        return $env->value;
    }


    public function setFrontEnv($name,$value){
        $env = FrontEnv::firstOrCreate(
            [
                'name' => $name
            ],
            [
                'value' => $value
            ]
        );
        $env->value = $value;
        $env->save();

        return $env;
    }
    public function getFrontEnv($name){
        $env = FrontEnv::find($name);
        if(empty($env) == true){
            return "";
        }

        return $env->value;
    }

    public function getBackend(Request $request){
        $app_name = $this->getBackendEnv('APP_NAME');
        $base_description = $this->getBackendEnv('BASE_DESCRIPTION');
        $app_url = $this->getBackendEnv('APP_URL');
        $app_keyword = $this->getBackendEnv('APP_KEYWORD');

        $aws_access_key = substr($this->getBackendEnv('AWS_ACCESS_KEY_ID'),0,4) . "********";
        $aws_secret_access_key = substr($this->getBackendEnv('AWS_SECRET_ACCESS_KEY'),0,4) . "********";
        $aws_region = $this->getBackendEnv('AWS_DEFAULT_REGION');
        $aws_bucket = $this->getBackendEnv('AWS_BUCKET');
        $aws_s3_uri = $this->getBackendEnv('BASE_AWS_S3_URI');
        $aws_mail_from_address = $this->getBackendEnv('MAIL_FROM_ADDRESS');
        $aws_mail_from_name = $this->getBackendEnv('MAIL_FROM_NAME');

        return response()->json([
            'data' => [
                'web' => [
                    'name' => $app_name,
                    'url' => $app_url,
                    'description' => $base_description,
                    'keyword' => $app_keyword,
                ],
                'aws' => [
                    'accessKey' => $aws_access_key,
                    'secretKey' => $aws_secret_access_key,
                    'region' => $aws_region,
                    'bucket' => $aws_bucket,
                    'imageURI' => $aws_s3_uri,
                    'senderEmail' => $aws_mail_from_address,
                    'senderName' => $aws_mail_from_name,
                ]
            ]
        ]);
    }

    public function getFront(Request $request){
        $app_name = $this->getFrontEnv('VUE_APP_NAME');
        $base_mainnet = $this->getFrontEnv('VUE_APP_BASE_MAINNET');

        $single_nft_contract_address = $this->getFrontEnv('VUE_APP_SINGLENFT_CONTRACT_ADDRESS');
        $eth_single_nft_contract_address = $this->getFrontEnv('VUE_APP_ETH_SINGLENFT_CONTRACT_ADDRESS');
        $klay_single_nft_contract_address = $this->getFrontEnv('VUE_APP_KLAY_SINGLENFT_CONTRACT_ADDRESS');
        $bsc_single_nft_contract_address = $this->getFrontEnv('VUE_APP_BSC_SINGLENFT_CONTRACT_ADDRESS');
        $matic_single_nft_contract_address = $this->getFrontEnv('VUE_APP_MATIC_SINGLENFT_CONTRACT_ADDRESS');

        $multi_nft_contract_address = $this->getFrontEnv('VUE_APP_MULTINFT_CONTRACT_ADDRESS');
        $eth_multi_nft_contract_address = $this->getFrontEnv('VUE_APP_ETH_MULTINFT_CONTRACT_ADDRESS');
        $klay_multi_nft_contract_address = $this->getFrontEnv('VUE_APP_KLAY_MULTINFT_CONTRACT_ADDRESS');
        $bsc_multi_nft_contract_address = $this->getFrontEnv('VUE_APP_BSC_MULTINFT_CONTRACT_ADDRESS');
        $matic_multi_nft_contract_address = $this->getFrontEnv('VUE_APP_MATIC_MULTINFT_CONTRACT_ADDRESS');

        $base_wallet = $this->getFrontEnv('VUE_APP_BASE_WALLET');
        $base_api_uri = $this->getFrontEnv('VUE_APP_BASE_API_URI');
        $base_exchange_api_uri = $this->getFrontEnv('VUE_APP_BASE_EXCHANGE_API_URI');

        $company_name = $this->getFrontEnv('VUE_APP_COMPANY_NAME');
        $company_text = $this->getFrontEnv('VUE_APP_COMPANY_TEXT');
        $company_address = $this->getFrontEnv('VUE_APP_COMPANY_ADDRESS');
        $company_number = $this->getFrontEnv('VUE_APP_COMPANY_NUMBER');
        $company_description = $this->getFrontEnv('VUE_APP_COMPANY_TEXT');
        $company_phone = $this->getFrontEnv('VUE_APP_COMPANY_PHONE');
        $company_email = $this->getFrontEnv('VUE_APP_COMPANY_EMAIL');
        $company_facebook = $this->getFrontEnv('VUE_APP_COMPANY_FACEBOOK');
        $company_twitter = $this->getFrontEnv('VUE_APP_COMPANY_TWITTER');
        $company_instagram = $this->getFrontEnv('VUE_APP_COMPANY_INSTAGRAM');
        $company_blog = $this->getFrontEnv('VUE_APP_COMPANY_BLOG');
        $company_telegram = $this->getFrontEnv('VUE_APP_COMPANY_TELEGRAM');
        $footer_text = $this->getFrontEnv('VUE_APP_FOOTER_TEXT');

        $analytics = $this->getFrontEnv('ANALYTICS');
        $service = $this->getFrontEnv('VUE_APP_SERVICE');
        $privacy = $this->getFrontEnv('VUE_APP_PRIVACY');

        return response()->json([
            'data' => [
                'web' => [
                    'name' => $app_name,
                    'mainnet' => $base_mainnet,
                    'single_nft_contract_address' => $single_nft_contract_address,
                    'eth_single_nft_contract_address' => $eth_single_nft_contract_address,
                    'klay_single_nft_contract_address' => $klay_single_nft_contract_address,
                    'bsc_single_nft_contract_address' => $bsc_single_nft_contract_address,
                    'matic_single_nft_contract_address' => $matic_single_nft_contract_address,
					
                    'multi_nft_contract_address' => $multi_nft_contract_address,
                    'eth_multi_nft_contract_address' => $eth_multi_nft_contract_address,
                    'klay_multi_nft_contract_address' => $klay_multi_nft_contract_address,
                    'bsc_multi_nft_contract_address' => $bsc_multi_nft_contract_address,
                    'matic_multi_nft_contract_address' => $matic_multi_nft_contract_address,
                    'wallet' => $base_wallet,
                    'api_uri' => $base_api_uri,
                    'exchange_api_uri' => $base_exchange_api_uri,
                ],
                'company' => [
                    'name' => $company_name,
                    'text' => $company_text,
                    'address' => $company_address,
                    'number' => $company_number,
                    'description' => $company_description,
                    'phone' => $company_phone,
                    'email' => $company_email,
                    'facebook' => $company_facebook,
                    'twitter' => $company_twitter,
                    'instagram' => $company_instagram,
                    'blog' => $company_blog,
                    'telegram' => $company_telegram,
                    'footer_text' => $footer_text,
                ],
                'analytics' => [
                    'source' => $analytics,
                ],
                'service' => [
                    'source' => $service,
                ],
                'privacy' => [
                    'source' => $privacy,
                ],
            ]
        ]);
    }

    public function setBackendWeb($request){
        if(empty($request->name) == true){
            return response()->json([
                'error' => [
                    'message' => '마켓 이름을 입력해주세요'
                ],
            ]);
        }else if(empty($request->description) == true){
            return response()->json([
                'error' => [
                    'message' => '마켓 설명을 입력해주세요'
                ],
            ]);
        }else if(empty($request->url) == true){
            return response()->json([
                'error' => [
                    'message' => '마켓 URL 입력해주세요'
                ],
            ]);
        }else if(empty($request->keyword) == true){
            return response()->json([
                'error' => [
                    'message' => '마켓 키워드를 입력해주세요'
                ],
            ]);
        }

        $this->setBackendEnv('APP_NAME',$request->name);
        $this->setBackendEnv('BASE_DESCRIPTION',$request->description);
        $this->setBackendEnv('APP_URL',$request->url);
        $this->setBackendEnv('APP_KEYWORD',$request->keyword);

        return false;
    }

    public function setBackendAws(Request $request){
        if(empty($request->accessKey) == true){
            return response()->json([
                'error' => [
                    'message' => '액세스 키를 입력해주세요'
                ],
            ]);
        }else if(empty($request->secretKey) == true){
            return response()->json([
                'error' => [
                    'message' => '시크릿 키를 입력해주세요'
                ],
            ]);
        }else if(empty($request->region) == true){
            return response()->json([
                'error' => [
                    'message' => '리전을 입력해주세요'
                ],
            ]);
        }else if(empty($request->bucket) == true){
            return response()->json([
                'error' => [
                    'message' => '버킷을 입력해주세요'
                ],
            ]);
        }else if(empty($request->senderEmail) == true){
            return response()->json([
                'error' => [
                    'message' => '전송자 이메일을 입력해주세요'
                ],
            ]);
        }else if(empty($request->senderName) == true){
            return response()->json([
                'error' => [
                    'message' => '전송자 이름을 입력해주세요'
                ],
            ]);
        }else if(empty($request->imageURI) == true){
            return response()->json([
                'error' => [
                    'message' => '이미지 URI를 입력해주세요'
                ],
            ]);
        }

        if(substr($request->accessKey,-4) != '****'){
            $this->setBackendEnv('AWS_ACCESS_KEY_ID',$request->accessKey);
        }

        if(substr($request->secretKey,-4) != '****'){
            $this->setBackendEnv('AWS_SECRET_ACCESS_KEY',$request->secretKey);
        }

        $this->setBackendEnv('AWS_DEFAULT_REGION',$request->region);
        $this->setBackendEnv('AWS_SES_REGION',$request->region);
        $this->setBackendEnv('AWS_BUCKET',$request->bucket);
        $this->setBackendEnv('MAIL_FROM_ADDRESS',$request->senderEmail);
        $this->setBackendEnv('MAIL_FROM_NAME',$request->senderName);
        $this->setBackendEnv('BASE_AWS_S3_URI',$request->imageURI);
        $this->setBackendEnv('IS_AWS_S3',1);

        return false;
    }

    public function setBackend(Request $request){
        if($request->type == 'web'){
            $result = $this->setBackendWeb($request);
        }else if($request->type == 'aws'){
            $result = $this->setBackendAws($request);
        }

        if($result != false){
            return $result;
        }

        return response()->json([
            'data' => true,
        ]);
    }

    public function setFrontCompany($request){

        $this->setFrontEnv('VUE_APP_COMPANY_NAME',$request->name);
        $this->setFrontEnv('VUE_APP_COMPANY_ADDRESS',$request->address);
        $this->setFrontEnv('VUE_APP_COMPANY_NUMBER',$request->number);
        $this->setFrontEnv('VUE_APP_COMPANY_TEXT',$request->description);
        $this->setFrontEnv('VUE_APP_COMPANY_PHONE',$request->phone);
        $this->setFrontEnv('VUE_APP_COMPANY_EMAIL',$request->email);
        $this->setFrontEnv('VUE_APP_COMPANY_FACEBOOK',$request->facebook);
        $this->setFrontEnv('VUE_APP_COMPANY_TWITTER',$request->twitter);
        $this->setFrontEnv('VUE_APP_COMPANY_INSTAGRAM',$request->instagram);
        $this->setFrontEnv('VUE_APP_COMPANY_BLOG',$request->blog);
        $this->setFrontEnv('VUE_APP_COMPANY_TELEGRAM',$request->telegram);
        $this->setFrontEnv('VUE_APP_FOOTER_TEXT',$request->footer_text);

        return false;
    }

    public function setFrontAnalytics($request){
        $this->setFrontEnv('ANALYTICS',$request->source);
        return false;
    }

    public function setFrontService($request){
        $this->setFrontEnv('VUE_APP_SERVICE',$request->source);
        return false;
    }

    public function setFrontPrivacy($request){
        $this->setFrontEnv('VUE_APP_PRIVACY',$request->source);
        return false;
    }

    public function setFront(Request $request){
        if($request->type == 'web'){
            $result = $this->setFrontWeb($request);
        }else if($request->type == 'company'){
            $result = $this->setFrontCompany($request);
        }else if($request->type == 'analytics'){
            $result = $this->setFrontAnalytics($request);
        }else if($request->type == 'service'){
            $result = $this->setFrontService($request);
        }else if($request->type == 'privacy'){
            $result = $this->setFrontPrivacy($request);
        }

        if($result != false){
            return $result;
        }

        return response()->json([
            'data' => true,
        ]);
    }

    public function setNftUpload($request){
        if(empty($request->auth_nft_size) == true){
            return response()->json([
                'error' => [
                    'message' => '인증된 저자의 NFT 업로드 최대 사이즈를 입력해주세요'
                ],
            ]);
        }else if(empty($request->unauth_nft_size) == true){
            return response()->json([
                'error' => [
                    'message' => '미인증된 저자의 NFT 업로드 최대 사이즈를 입력해주세요'
                ],
            ]);
        }else if(empty($request->profile_size) == true){
            return response()->json([
                'error' => [
                    'message' => '프로필 사진 업로드 최대 사이즈를 입력해주세요'
                ],
            ]);
        }else if(empty($request->cover_size) == true){
            return response()->json([
                'error' => [
                    'message' => '커버 사진 업로드 최대 사이즈를 입력해주세요'
                ],
            ]);
        }

        $this->setBackendEnv('UPLOAD_SIZE_AUTH_AUTHORS',$request->auth_nft_size);
        $this->setBackendEnv('UPLOAD_SIZE_UNAUTH_AUTHORS',$request->unauth_nft_size);
        $this->setBackendEnv('UPLOAD_SIZE_PROFILE',$request->profile_size);
        $this->setBackendEnv('UPLOAD_SIZE_COVER',$request->cover_size);
        $this->setBackendEnv('UPLOAD_FILTER_ADDRESS',$request->address_filter);

        return false;
    }

    public function setNftFilter($request){
        $this->setBackendEnv('UPLOAD_FILTER_TEXT',strip_tags($request->source));
        return false;
    }

    public function setNftIP($request){
        $this->setBackendEnv('UPLOAD_FILTER_IP',strip_tags($request->source));
        return false;
    }
	
    public function setNftLayer($request){
        $this->setBackendEnv('UPLOAD_FILTER_LAYER',strip_tags($request->source));
        return false;
    }

    public function setNft(Request $request){
        if($request->type == 'nft'){
            $result = $this->setNftEnv($request);
        }else if($request->type == 'category'){
            $result = $this->setNftCategory($request);
        }else if($request->type == 'upload'){
            $result = $this->setNftUpload($request);
        }else if($request->type == 'filter'){
            $result = $this->setNftFilter($request);
        }else if($request->type == 'ip'){
            $result = $this->setNftIP($request);
        }else if($request->type == 'layer'){
            $result = $this->setNftLayer($request);
        }

        if($result != false){
            return $result;
        }

        return response()->json([
            'data' => true,
        ]);
    }

    public function deleteCategory(Request $request){
        if(empty($request->name) == true){
            return response()->json([
                'error' => [
                    'message' => '카테고리 식별 이름을 입력해주세요'
                ],
            ]);
        }

        $categoryEnv = CategoryEnv::find($request->name);
        if(empty($categoryEnv) == true){
            return response()->json([
                'error' => [
                    'message' => '이미 삭제되었거나 없는 카테고리 입니다'
                ],
            ]);
        }

        $categoryEnv->delete();

        return response()->json([
            'data' => $categoryEnv,
        ]);
    }

    public function addCategory(Request $request){
        $pattern = '/([a-zA-Z])+/';
        preg_match_all($pattern, $request->name, $match);
        $request->name =implode('', $match[0]);
        //$request->name = preg_replace("/[^a-zA-Z/s", "", $request->name);

        if(empty($request->name) == true){
            return response()->json([
                'error' => [
                    'message' => '식별자는 소문자 알파벳만 입력할수 있습니다'
                ],
            ]);
        }else if(empty($request->value) == true){
            return response()->json([
                'error' => [
                    'message' => '이름을 입력해주세요'
                ],
            ]);
        }else if(empty($request->footer) == true){
            return response()->json([
                'error' => [
                    'message' => '하단 메뉴 이름을 입력해주세요'
                ],
            ]);
        }

        $categoryEnv = CategoryEnv::find($request->name);
        if(empty($categoryEnv) == false){
            return response()->json([
                'error' => [
                    'message' => '같은 식별자로 이미 존재하는 카테고리가 있습니다'
                ],
            ]);
        }

        $categoryEnv = new CategoryEnv();
        $categoryEnv->name  = $request->name;
        $categoryEnv->value = $request->value;
        $categoryEnv->footer = $request->footer;
        $categoryEnv->save();

        return response()->json([
            'data' =>[
                'name' => $request->name,
                'value' => $request->value,
                'footer' => $request->footer,
            ],
        ]);
    }

	public function getToken(Request $request){
		$tokens = PayToken::all();
		$data = [];
		foreach($tokens as $token){
			array_push($data,$token);
		}
		return response()->json([
		  'data' => $tokens
		]);
	}

	public function addToken(Request $request){
		if(empty($request->name) == true){
			return response()->json([
				'error' => [
					'message' => '이름을 입력해주세요'
				],
			]);
		}else if(empty($request->symbol) == true){
			return response()->json([
				'error' => [
					'message' => '심볼을 입력해주세요'
				],
			]);
		}else if(empty($request->decimals) == true){
			return response()->json([
				'error' => [
					'message' => '소수점을 입력해주세요'
				],
			]);
		}else if(empty($request->tokenAddress) == true){
			return response()->json([
				'error' => [
					'message' => '토큰 주소를 입력해주세요.'
				],
			]);
		}

		$PayToken = PayToken::where('tokenAddress',$request->tokenAddress)->first();
		if(empty($PayToken) == false){
			return response()->json([
				'error' => [
					'message' => '같은 주소로로 이미 존재하는 토큰가 있습니다'
				],
			]);
		}

		$extension = strtolower($request->token_file->extension());
		$filename = Str::random(30) . "." . $extension;

		if(empty(envDB('IS_AWS_S3')) == true){
			$path = $request->token_file->storeAs('token_file',$filename,'public');
			if($path == false){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
			}

			$path = storage_path('app/public/'.$path);
		}else{
			$path = $request->token_file->path();
			if(Storage::disk('s3')->put('/token-files/'.$filename,file_get_contents($path),'public') == false){
				return response()->json([
					'error' => [
						'message' => "파일 저장에 실패하였 습니다"
					]
				]);
			}
		}

		$PayToken = new PayToken();
		$PayToken->name  = $request->name;
		if(empty($request->net) == true){
			$PayToken->net  = envDB('BASE_MAINNET');
		}else{
			$PayToken->net  = $request->net;
		}
		
		$PayToken->symbol = $request->symbol;
		$PayToken->decimals  = $request->decimals;
		$PayToken->img_url = envDB('BASE_AWS_S3_URI') . '/token-files/' . $filename;
		$PayToken->tokenAddress = $request->tokenAddress;
		$PayToken->save();

		return response()->json([
			'data' =>[
				'name' => $PayToken->name,
				'symbol' => $PayToken->symbol,
				'decimals' => $PayToken->decimals,
				'img_url' => $PayToken->img_url,
				'tokenAddress' => $PayToken->tokenAddress,
			],
		]);
	}

	public function deleteToken(Request $request){
		if(empty($request->tokenAddress) == true){
			return response()->json([
				'error' => [
					'message' => '토큰 식별 주소을 입력해주세요'
				],
			]);
		}

		$PayToken = PayToken::where('tokenAddress',$request->tokenAddress);
		if(empty($PayToken) == true){
			return response()->json([
				'error' => [
					'message' => '이미 삭제되었거나 없는 토큰 입니다'
				],
			]);
		}

		$PayToken->delete();

		return response()->json([
			'data' => $PayToken,
		]);
	}
}
