<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model
{
    use HasFactory;
	
    protected $table = 'user_privileges';

	protected $fillable = [
        'id',
        'user_id',
        'authority',
    ];
	
	public function user(){
		return User::find($this->user_id);
	}
}
