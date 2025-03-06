<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class WalletController extends Controller
{

  public function checkWalletBalance(Request $request)
  { 
    
    $user = UserService::getUserWalletBalance($request->user());

    if(!$user || !$user->wallet) {

      return $this->errorResponse(
        status: false,
        message: "Sorry, You don't have an active wallet, kindly fund your account to activate your wallet",
        statusCode:404,
      );

    }

    return $this->successResponse(
      status: true,
      message: 'user details and wallet balance fetched successfully',
      statusCode:200,
      data: [
        'data' => new UserResource($user),
      ]
    );
     
  }



}