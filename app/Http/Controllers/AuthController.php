<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use App\Notifications\ActivateEmail;
use App\Notifications\ResetPassword;
use App\Mail\RegisterUserMail;
use App\Mail\ForgottenPassword;
use Illuminate\Support\Str;
use App\http\Controllers\BaseController as BaseController;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ForgetPassword;
use App\Models\UserActivateToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Http;


class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'firstname' => 'required',
                'lastname' => 'required',
                'username' => 'required|unique:users|max:30',
                'email' => 'required|email',
                'phone' => 'numeric',
                'country' => 'required',
                'profile_image'=>'file|mimes:jpeg,bmp,png,pdf,doc,docx',
                'password' => 'required',
                'c_password' => 'required|same:password'
            ]);
        if ($validator->fails())
        {
            return $this->sendError($validator->errors()->first());
        }
        $input = $request->all();
        $input['fcm_token']=$request->fcm_token;
        $input['password'] = Hash::make($input['password']);
        if($request->hasFile('profile_image'))
        {
            $image_name='profile_image-'.time().'.'.$request->profile_image->extension();
            $request->profile_image->move(public_path('/upload/profile_images'),$image_name);
            $input['profile_image']=$image_name;
        }
        $user = User::create($input);
        if($user)
        {
            $token=random_int(1000,9999);
            $newToken=new UserActivateToken();
            $newToken->user_id=$user->id;
            $newToken->token=$token;
            $newToken->save();
       //     Mail::to(users:$user->email)->send(new RegisterUserMail($user,$token));
        }
        $success['token'] = $user->createToken('secret')->plainTextToken;
        $success['id'] = $user->id;
        $success['fcm_token'] = $user->fcm_token;
        $success['username'] = $user->username;
        $success['firstname'] = $user->firstname;
        $success['lastname'] = $user->lastname;
        $success['email'] = $user->email;
        $success['phone'] = $user->phone;
     //   $success['address'] = $user->address;
        $success['country'] = $user->country;
       // $success['city'] = $user->city;
        $success['profile_image'] = $user->profile_image;
        $success['code']=$token;
        return $this->sendResponse($success, 'register send email');
    }
    public function refreshToken(Request $request)
    {
                $user_id=$request->user_id;
                $fcm_token=$request->fcm_token;
                $user=User::where('id',$user_id)->update(['fcm_token'=>$request->fcm_token]);
                return User::find($user_id);
    }
    public function sendNotification(Request $request)

    {
        $user_id=$request->user_id;
        $fcm_token=User::find($user_id)->fcm_token;
        $server_key=env('FCM_SERVER_KEY');
        $fcm=Http::acceptJson()->withToken($server_key)->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'to'=>$fcm_token,
                'notifications'=>
                [
                    'title'=>'hello',
                    'body'=>'welcome notify'
                ]
            ]
                );
                
                return json_decode($fcm);
    }
    public function sendNotifyBroadcast(Request $request)
    {
        $user_ids=$request->user_ids;
        $fcm_tokens=User::find($user_ids)->pluck('fcm_token');
        $server_key=env('FCM_SERVER_KEY');
        $fcm=Http::acceptJson()->withToken($server_key)->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'to'=>$fcm_tokens,
                'notifications'=>
                [
                    'title'=>'hello',
                    'body'=>'welcome notify broadcast'
                ]
            ]
                );
                return json_decode($fcm);
    }
    public function ActivateEmail(Request $request)
    {
        $checkToken=UserActivateToken::where(['token'=>$request->code])->first();
        if ($checkToken) 
        {
            $user_id=$checkToken->user_id;
            $user=User::where(['id'=>$user_id])->first();
            $user->email_verified_at=Carbon::now();
            $user->save();
            // to delete activate  $checkToken->delete();
            //notify (database,broadcast)
           // $user->notify(new ActivateEmail($user));
           $success['token'] = $user->createToken('secret')->plainTextToken;
           $success['id'] = $user->id;
           return $this->sendResponse($success, 'login Successfully!');
        }
    }

    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            
            $success['token'] = $user->createToken('secret')->plainTextToken;
            $success['id'] = $user->id;
            $success['username'] = $user->username;
            $success['firstname'] = $user->firstname;
            $success['lastname'] = $user->lastname;
            $success['email'] = $user->email;
            $success['phone'] = $user->phone;
            
            $success['country'] = $user->country;
          
            $success['profile_image'] = $user->profile_image;
            return $this->sendResponse($success, 'login Successfully!');
        }
        else
        {
            return $this->sendError(' Error', ['error', 'Unauthorized']);
        }
    }

    public function showinf()
    {
        $user=Auth::User();  //get : all user  first : user id=1   not userAuth
        
        return $this->sendResponse($user, 'AuthUser INF');
    }

    public function forgotPasswordCreate(Request $request)
    {
        $user=User::where('email',$request->email)->first();
        if($user)
        {
            //error : Property [email] does not exist on the Eloquent builder instance
            //solve : get email by array this error and not found get or fast 
            //$user=User::where(['email'=>$request->email]);
            //$user=User::where('email',$request->email)->first();
            $Password=ForgetPassword::updateOrCreate(
                ['email'=>$request->email],
                    [
                        'email'=>$request->email,
                        'code'=>random_int(1000,9999),
                    ]
                    ); 
       //  Mail::to($user->email)->send(new ForgottenPassword($Password));
       //  $user->notify(new ResetPassword($user));
         return $this->sendResponse($Password, 'link reset sent');
        }
        else
        {
            return $this->sendError(' Error', ['error', 'Unauthorized']);
        }

     }
 

   public function forgotPasswordToken(Request $request)
     {
        $validator = Validator::make($request->all(),
            [
                'email'=>'required',
                'code'=>'required',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
        if ($validator->fails())
        {
            return $this->sendError('Validator Error', $validator->errors());
        }
        $code=$request->code;
         $checkReset=ForgetPassword::where([
             'code'=>$code,
             'email'=>$request->email,
         ])->first();
         if(!$checkReset)
         {
             return 'details not match';
         }
         $user=User::where('email',$request->email)->first();
         if(!$user)
         {
             return 'user not found';
         }
         $user->password=bcrypt($request->password);
         $user->c_password=bcrypt($request->c_password);
         $user->save();
         $success['token'] = $user->createToken('secret')->plainTextToken;
            $success['id'] = $user->id;
            $success['username'] = $user->username;
            $success['firstname'] = $user->firstname;
            $success['lastname'] = $user->lastname;
            $success['email'] = $user->email;
            $success['phone'] = $user->phone;
            
            $success['country'] = $user->country;
          
            $success['profile_image'] = $user->profile_image;
         $checkReset->delete();
         return $this->sendResponse($success, 'Reset Password Successfully!');
    }
 
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
       // $request->user()->currentAccessToken()->delete();
        return $this->sendResponse('Logout','USER logout Successfully!');

    }
    public function resetPassword(Request $request)
    {
        $validator=Validator::make(
            $request->all(),
            [
                'oldpassword'=>'required',
                'newpassword'=>'required',
                'c_newpassword'=>'required|same:password'
        
            ]);

            $user=Auth::User();
            if ($request->oldpassword=$user->password)
            {
            $user->password=bcrypt($request->newpassword);
             $user->save();  
             return $this->sendResponse('r','reset password Successfully!'); 
            }
            return 'old password incorrect';
        }

    public function resetEmail()  
    {
        $validator=Validator::make(
            $request->all(),
            [
                'password'=>'required',
                'newEmail'=>'required',
            ]);

            $user=Auth::User();
            $user->email_verified_at=Carbon::now();
            if ($request->password=$user->password)
            {
                $user->email=$request->newEmail;
            }


    }

}
