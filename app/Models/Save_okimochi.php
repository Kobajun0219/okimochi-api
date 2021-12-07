<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Save_okimochi extends Model
{
    use HasFactory;
    protected $fillable = [
        'okimochi_id',
        'user_id'
    ];

    public function okimochi()
    {
        return $this->belongsTo(Okimochi::class);
    }
}
