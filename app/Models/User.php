<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
//class User extends Authenticatable
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded=[];
   /* protected $fillable = [
        'name',
        'email',
        'password',
    ]; */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'c_password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
    ];
    public function sendPasswordResetNotification($token)
    {

        $url = 'https://spa.test/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants')
            ->latest('last_message_id')
            ->withPivot([
                'role', 'joined_at'
            ]);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'recipients')
            ->withPivot([
                'read_at', 'deleted_at',
            ]);
    }

    public function getAvatarUrlAttribute()
    {
        return 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . $this->name;
    }

    public function friends()
    {
        return $this->belongsToMany(User::class,'friend_user','user_id','friend_id' );
    }
    public function status()
    {
        return $this->hasMany('status');
    }
    public function posts()
    {
        return $this->hasMany('App\Models\Post','user_id','id');
    }
    public function events()
    {
        return $this->hasMany('App\Models\Event','user_id','id');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\Comment','user_id','id')->whereNull('parent_id');
    }
    public function pictures()
    {
        return hasOne('App\Models\Picture');

    }
    public function pushNotification($title,$body,$message){

		$token = $this->fcm_token;
		

		if($token == null) return;

		$data['notification']['title']= $title;
		$data['notification']['body']= $body;
		$data['notification']['sound']= true;
		$data['priority']= 'normal';
		$data['data']['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
		$data['data']['message']=$message;
		$data['to'] = $token;
		

		$http = new \GuzzleHttp\Client(['headers'=>[
			'Centent-Type'=>'application/json',
			'Authorization'=>'key=AAAAuWiet7w:APA91bFMtMwvQJHHYe7VBzAMCy5wBRqRDyAXmnooA2Tpn2X0Tap9_o5WWvTuceJAsHDehnEWA2CZHpQ6jF65jg0sfn3mnfIRsk87lz0CeC4eNBh482pUkFrH_mCoEpWualUyvderE8Za'

		]]);
		try {
            $response = $http->post('https://fcm.googleapis.com/fcm/send', [ 'json' =>
                    $data
            ]);
            return $response->getBody();
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
			// return $e->getCode();
            if ($e->getCode() === 400) {
                return response()->json(['ok'=>'0', 'erro'=> 'Invalid Request.'], $e->getCode());
            } else if ($e->getCode() === 401) {
                return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
            }
            return response()->json('Something went wrong on the server.', $e->getCode());
        }        

	}
}

