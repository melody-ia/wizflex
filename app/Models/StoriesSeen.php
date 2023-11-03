<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoriesSeen extends Model
{
    use HasFactory;

    protected $table = 'stories_seen';

    protected $fillable = [
        'id',
        'story_id',
        'address',
        'seen',
    ];


}
