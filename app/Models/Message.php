<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $fillable = ['body','user_id','conversation_id','read'];

	public function conversation(){
		
		return $this->belongsTo('App\Models\Conversation');
	}
}
