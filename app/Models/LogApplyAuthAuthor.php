<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTimeInterface;

class LogApplyAuthAuthor extends Model
{
    use HasFactory;
	
	
    protected $table = 'log_apply_auth_author';
	
	protected $fillable = [
        'address',
        'name',
        'phon',
        'email',
        'auth',
        'description',
        'memo',
    ];
	
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}