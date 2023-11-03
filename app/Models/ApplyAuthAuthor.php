<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTimeInterface;

class ApplyAuthAuthor extends Model
{
    use HasFactory;
	
	
    protected $table = 'apply_auth_author';
	
	protected $fillable = [
        'address',
        'name',
        'phon',
        'email',
        'auth',
        'description',
    ];
	
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}