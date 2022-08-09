<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Hash;
use Validator;
use App\Http\Controllers\BaseController as BaseController;

class UserController extends BaseController
{
    public function register(storeUserRequest $request){
		$user = User::create([
			'name'=>$request['name'],
			'email'=>$request['email'],
			'password'=>Hash::make($request['password']),
		]);
		return new UserResource($user);
	}


	public function current()  {
		return new UserResource(auth()->user());
	}

	public function update(Request $request){
		$request->validate([
			'name'=>'required',
			'email'=>'required|email|unique:users,email,'.auth()->id()
		]);
		$user = User::find(auth()->id());
		$user->name = $request['name'];
		$user->email = $request['email'];
		$user->save();
		return new UserResource($user);

	}
	public function myProfile()
	{
		$id=Auth::id();
		$Profile=User::where('id',$id)->select('id','firstname','lastname','username','country','email','profile_image')
            ->with([
                'posts' => function($builder) {$builder->withCount('comments','likes');},
                ])->get();
				return response()->json(['profile' => $Profile]);
	}
	public function editprofile(Request $request)
	{
		$user=Auth::user();
		$validator = Validator::make($request->all(),
            [
                'username'=>'required',
                'profile_image'=>'file|mimes:jpeg,bmp,png,pdf,doc,docx',
            ]);
        if ($validator->fails())
        {
            return $this->sendError('Validator Error', $validator->errors());
        }
		if($request->hasFile('profile_image'))
		{
			if ($user->profile_image)
			{
				$old_path=public_path().'/upload/profile_images/'.$user->profile_image;
				if(File::exists($old_path))
				{
					File::delete($old_path);
				}
			}
			$image_name='profile_image-'.time().'.'.$request->profile_image->extension();
			$request->profile_image->move(public_path('/upload/profile_images'),$image_name);
		}
		else 
		{
			$image_name=$user->profile_image;
		}
		$user->update([
			'username'=>$request->username,
			'profile_image'=>$image_name,
		]);
		
        return response()->json([
			'message'=>'updated profile successfully',],200);  
	}

	public function ChangePassword(Request $request)
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
			$user->c_password=bcrypt($request->c_newpassword);
             $user->save();  
			 return 'reset password Successfully!';
            }
            return 'old password incorrect';
	}
	public function search(Request $request)
	{
		$data = $request->get('data');
        $users = User::where('username', 'like', "%{$data}%")->select('username','profile_image')->paginate(10);
         return response()->json(['users'=>$users]); 

    }
	public function fcmToken(Request $request)
	{
		$user = User::find(auth()->id());
		$user->update(['fcm_token'=>$request['fcm_token']]);
		return response()->json('fcm updated successfully',200);
	}


	public function Profile()
	{	}


	public function DeleteAccount()
	{	}
}
