<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $fillable = [
        'user_id','friend_id'
    ];

    public function user(){

        return $this->belongsTo(User::class,'friend_id','id');
    }

}
