<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ResponseHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller 
{
    public function LoginUser(Request $request) 
    {

      $validator = Validator::make($request->all(), [
        'email' => ['required', 'email',],
        'password' => ['required', 'string', 'min:8'],
      ]);

      if($validator->fails()) {
        
        return   $this->errorResponse(
          status: false,
          message: 'validation error',
          statusCode: 422,
          errors: [
            'errors' => $validator->errors()
            ]
          
        );

      }


      if(!Auth::attempt($request->only(['email', 'password']))) {
           return $this->errorResponse(
            status: false,
            message: 'Invalid Email or Password',
            statusCode: 403,
           );
      }

      $user = Auth::user();

      $token = $user->createToken(config('app.name', 'walletApi'))->plainTextToken;

      return $this->successResponse(
        status: true,
        message: 'User logged in succesfully',
        statusCode: 200,
        data: [
          'token' => $token,
          'user' => new UserResource($user),
        ],
      
      );


    }
}