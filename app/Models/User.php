<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use DateTimeInterface;

class User extends Authenticatable
{
    use Notifiable;
	
	protected $fillable = [
        'email',
        'password',
        'name',
        'phon',
        'remember_token',
        'last_login_at',
    ];
	
	
	public function isAdmin(){
		$privilege = UserPrivilege::where('user_id',$this->id)->first();
		if(empty($privilege) == true){
			return false;
		}else if($privilege->authority == 'admin_super' || $privilege->authority == 'admin'){
			return true;
		}
		
		return false;
	}
	public function isSuperAdmin(){
		$privilege = UserPrivilege::where('user_id',$this->id)->first();
		if(empty($privilege) == true){
			return false;
		}else if($privilege->authority == 'admin_super'){
			return true;
		}
		
		return false;
	}
	
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
