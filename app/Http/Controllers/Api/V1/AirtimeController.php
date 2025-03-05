<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentDesc;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\PaymentSuccessNotification;
use App\Services\PaymentService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AirtimeController extends Controller
{
  protected $paymentService;

  public function __construct(PaymentService $paymentService)
  {
      $this->paymentService = $paymentService;
  }

  public function purchaseAirtime(Request $request)  
  {
    $validator = Validator::make($request->all(), [
      'amount'  => ['required', 'numeric', 'min:1'],
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

  
    $wallet = Wallet::with('user')->where('user_id', $request->user()->id)->lockForUpdate()->first();
     
    if(!$wallet || !$wallet->user) {

      return $this->errorResponse(
        status: false,
        message: "Sorry, You don't have an active wallet, kindly fund your account to activate your wallet",
        statusCode:400,
      );

    }
    

    if($wallet->balance < $request->amount) {

      return $this->errorResponse(
        status: false,
        message: "Insufficient balance!!, kindly fund your wallet",
        statusCode:400,
      );
    }

    try {

      $response  =  $this->paymentService->handleAirtimePayment( wallet: $wallet, amount: $request->amount, response: true);

      if($response) {

        $userInvoice = UserService::getUserInvoice($request->user());

        DB::beginTransaction();

        $wallet->balance -= $request->amount;
        $wallet->save();

        $payload = [];

        $payload['user_id'] = $request->user()->id;
        $payload['amount'] =  $request->amount;
        $payload['status'] =  PaymentStatus::PAID;
        $payload['transaction_reference'] = $this->paymentService->trxRef(prefix: 'TXN');
        $payload['invoice'] = $userInvoice ? $userInvoice->invoice : $this->paymentService->trxRef(prefix: 'INV');
        $payload['description'] =  PaymentDesc::DESCRIPTION->value;
        $payload['purpose'] =  PaymentDesc::PURPOSE->value;
        $payload['transaction_date'] = date("Y-m-d H:i:s");

        Transaction::create($payload);

        $user = $wallet->user;

        $user->notify(new PaymentSuccessNotification(
          name: $user->name,
          amount:  $request->amount,
          invoice: $userInvoice->invoice,
          title: PaymentDesc::TITLE_PURCHASE->value,
          purpose: PaymentDesc::PURPOSE_AIRTIME->value,
          
        )); 

      
        DB::commit();

        return $this->successResponse(
          status: true,
          message: 'Airtime Purchase Completed successfully',
          data: [
            'data' => new UserResource(User::with('wallet')->find($request->user()->id)),
          ],

        );


      }


      return $this->errorResponse(
        status: false,
        message: 'OHHHH!!!!  ::: An error occurred while processing your airtime purchase. Please try again later or contact customer support!',
        statusCode:400,
      );


    }catch(Exception $e) {

      Log::error('airtime purchase error'. $e->getMessage());

      DB::rollBack();

      return $this->errorResponse(
        status: false,
        message: 'OHHHH!!!!  ::: An error occurred while processing your airtime purchase. Please try again later or contact customer support!',
        statusCode:400,
      );


    }



  }












}