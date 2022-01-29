<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_user_id',
        'receive_user_id',
        'status',
    ];

    public function request_user()
    {
        return $this->belongsTo(User::class, "request_user_id");
    }

    public function receive_user()
    {
        return $this->belongsTo(User::class, "receive_user_id");
    }
}
