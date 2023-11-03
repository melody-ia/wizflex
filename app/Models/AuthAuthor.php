<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTimeInterface;

class AuthAuthor extends Model
{
    use HasFactory;
	
	
    protected $table = 'auth_author';
	
	protected $fillable = [
        'address',
        'phon',
        'email',
    ];
	
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}