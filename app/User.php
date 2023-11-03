<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;
	
	protected function create(array $data){
		return User::create([
			'email' => $data['email'],
			'password' => Hash::make($data['password']),
		]);
	}
}
