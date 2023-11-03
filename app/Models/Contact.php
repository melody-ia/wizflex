<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTimeInterface;

class Contact extends Model
{
    use HasFactory;
	
    protected $table = 'contact';

	protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
    ];
	
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
