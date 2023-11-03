<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\StoriesSeen;

class Stories extends Model
{
    use HasFactory;

    protected $table = 'stories';

    protected $fillable = [
        'story_id',
        'address',
        'story_image',
        'swipeText',
    ];

    protected $dates = ['date_begin', 'date_end'];


    public function getDateYmd(){
//        $date = Carbon::createFromFormat('Y-m-d', $this->created_at);
//        return $date;
//        return $this->created_at;
        return date('Y-m-d', strtotime($this->created_at));
        return $this->date_begin;
    }

    protected $casts = [
        'created_at'  => 'datetime:Y-m-d'
    ];
}
