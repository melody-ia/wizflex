<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\User;
use App\Models\BtcWallet;

use BlockSDK;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

	public function register(Request $request)
	{
		$validator = $this->validator($request->all());
		if($validator->fails()){
			return response()->json([
				'error' => $validator->errors()
			]);
		}
		
		
		$bitcoin = BlockSDK::createBitcoin();
		$btcWallet = $bitcoin->createHdWallet([]);
		
		$bitcoin->createWalletAddress([
			'wallet_id' => $btcWallet['id'],
			'wif'       => $btcWallet['wif'],
		]);
		
		$user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

		BtcWallet::create([
			'id'       => $btcWallet['id'],
			'user_id'  => $user->id,
			'wif'      => $this->AES_Encode($btcWallet['wif'],$request->password),
			'mnemonic' => $btcWallet['mnemonic'],
			'password' => $request->password
		]);
		
		\Illuminate\Support\Facades\Auth::login($user,true);
		
		return response()->json([
			'user' => $user,
			'mnemonic'  => $btcWallet['mnemonic'],
		]);
	}
	
	function AES_Encode($str,$key)
	{
		return base64_encode(openssl_encrypt($str, "aes-256-cbc", $key, true, str_repeat(chr(0), 16)));
	}
}
