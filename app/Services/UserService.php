<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserService 
{
    public static function getUserInvoice($user)
    {
        
      return Transaction::with('user')->where('user_id', $user->id)->latest()->first();

    }

    public static function getUserWalletBalance($user)
    {
      
      
      return  User::with('wallet')->find($user->id);

    
    }



}