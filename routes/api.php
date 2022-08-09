<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\FriendRequestController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Auth
Route::post('register',[AuthController::class,'register']); //notify email
Route::post('activate',[AuthController::class,'ActivateEmail']);  //with notify dtabase broadcast
Route::post('login',[AuthController::class,'login'])->name('login'); 
Route::post('forgotpasswordCreate', [AuthController::class, 'forgotPasswordCreate']);//notify email 
Route::post('forgotpassword', [AuthController::class, 'forgotPasswordToken']);  //request code

// Protected Routes
Route::group(['middleware' => ['auth:sanctum']], function() {

    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Post
    Route::get('/posts', [PostController::class, 'index']); // all posts
    Route::post('/posts', [PostController::class, 'store']); // create post
    Route::get('/posts/{id}', [PostController::class, 'show']); // get single post
    Route::put('/posts/{id}', [PostController::class, 'update']); // update post
    Route::delete('/posts/{id}', [PostController::class, 'destroy']); // delete post

    // Comment
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']); // all comments of a post
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']); // create comment on a post
    Route::put('/comments/{id}', [CommentController::class, 'update']); // update a comment
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']); // delete a comment

    // Like
    Route::post('/posts/{id}/likes', [LikeController::class, 'likeOrUnlike']); // like or dislike back a post

    //FRIENDS 
    Route::post('friends', [FriendController::class, 'friends']);
    Route::post('friendrequest', [FriendRequestController::class, 'checkFriendRequest']);
    Route::post('friendrequests', [FriendRequestController::class, 'friendRequests']);
    Route::post('acceptfriendrequest', [FriendRequestController::class, 'acceptRequest']);
    Route::post('refusefriendrequest', [FriendRequestController::class, 'refuseRequest']);
    Route::post('cancelfriendrequest', [FriendRequestController::class, 'cancelFriendRequest']);
    Route::post('unfriend', [FriendController::class, 'unFriend']);
///


    Route::get('/myProfile',[UserController::class,'myProfile']); 
    Route::get('/Profile',[UserController::class,'Profile']); 
    Route::post('/editProfile',[UserController::class,'editProfile']);  //name,photo
    Route::post('/ChangePassword',[UserController::class,'ChangePassword']); 
    Route::post('/search',[UserController::class,'search']);





    //Messenger 
    Route::post('/upload', [PictureController::class, 'store']);
    Route::post('/update', [UserController::class, 'update']);
    Route::get('/user', [UserController::class, 'current']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);

    Route::post('/conversations/read', [ConversationController::class, 'makConversationAsReaded']);
    Route::post('messages', [MessageController::class, 'store']);
    Route::post('fcm', [UserController::class, 'fcmToken']);


    Route::post('/DeleteAccount',[UserController::class,'DeleteAccount']);

    //FCM 

});
Route::post('refresToken',[AuthController::class,'refreshToken']);
Route::post('sendNotification',[AuthController::class,'sendNotification']);
Route::post('sendNotifyBroadcast',[AuthController::class,'sendNotifyBroadcast']);

