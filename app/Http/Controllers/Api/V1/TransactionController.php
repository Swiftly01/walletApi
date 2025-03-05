<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{

  public function getTransactions(Request $request)
  { 
    
    /*

    $validator = Validator::make($request->query(), [
      'invoice' => ['required', 'string', 'regex:/^INV-\d{4}-[A-Z0-9]{4}-\d{4}-[A-Z0-9]{4}$/'],
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

    $validated = $validator->validated();
     */

    $transactions = Transaction::with('user')
                    ->where('user_id', $request->user()->id)
                   // ->where('invoice', $validated['invoice'])
                    ->where('status', PaymentStatus::PAID)
                    ->paginate(10);

    if($transactions->isEmpty()) {  

      return $this->errorResponse(
        status: false,
        message: "You don't have a transaction record yet!!",
        statusCode: 400,      
        );

    }


    return $this->successResponse(
      status: true,
      message: 'User transactions record fetched succesfully',
      data: [
        'data' => TransactionResource::collection($transactions),
      ],

    );


    


  }
}