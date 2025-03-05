<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletResource;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{

  public function checkWalletBalance(Request $request)
  { 
    
    $user = User::with('wallet')->find($request->user()->id);

    if(!$user->wallet) {

      return $this->successResponse(
        status: true,
        message: "Sorry, You don't have an active wallet, kindly fund your account to activate your wallet",
        statusCode:200,
      );

    }

    return $this->successResponse(
      status: true,
      message: 'User Details and wallet balance fetched Successfully',
      statusCode:200,
      data: [
        'data' => new UserResource($user),
      ]
    );
     
  }



}