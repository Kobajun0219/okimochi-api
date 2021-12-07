<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Okimochi extends Model
{
    use HasFactory;
    protected $fillable = [
        'who',
        'title',
        'message',
        'user_name',
        'user_id',
        'pic_name',
        'open_time',
        'open_place_name',
        'open_place_latitude',
        'open_place_longitude',
        'public',
    ];

    public function save_okimochis()
    {
        return $this->hasMany(Save_okimochi::class);
    }
}
