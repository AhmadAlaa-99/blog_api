<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
//use App\Http\Controllers\Controller as Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class BaseController
{
  public function sendResponse($result,$message)
  {
       $response=[
           'success'=>true,
           'data'=>$result,
           'message'=>$message
       ];
       return response()->json($response,200);

  }
  public function sendError($errorMessage)
  {
       $response=[
           'success'=>false,
           'data'=>null
       ];
       if(!empty($errorMessage))
       {
           $response['message']=$errorMessage;
       }
       return response()->json($response);

  }

}
