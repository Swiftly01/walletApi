<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Notifications\PaymentSuccessNotification;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\TransactionBeginning;

class PaymentController extends Controller
{

  protected $paymentService;

 public function __construct(PaymentService $paymentService)
  { 
    $this->paymentService = $paymentService;
    
  }


  public function handlePayment(Request $request) 
  {
      $validator = Validator::make($request->all(), [
        'amount' => ['required', 'numeric','min:1'],
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

    
    $user = $request->user();
    $secret_key = config('services.secret_key.key');
    $init_url =  "https://api.paystack.co/transaction/initialize";
    $callback_url = route('verify.txn');
    $charges = $this->paymentService->get_actual_charges($request->amount);
    $amount = $request->amount + $charges;
    $tx_ref = $this->paymentService->trxRef(prefix: 'TXN');
    $invoice = $this->paymentService->trxRef(prefix: 'INV');

    try {

      $this->paymentService->storeInitTransactions(
        user:$user,
        amount: $request->amount,
        txref: $tx_ref,
        inv: $invoice,
      );

      $response = $this->paymentService->initPaystackTransaction(
        user: $user,
        amount: $amount,
        tx_ref: $tx_ref,
        callback_url: $callback_url,
        secret_key: $secret_key,
        init_url: $init_url,
      );

      return  $this->paymentService->handleInitPaystackResponse($response);

  
    }catch(Exception $e) {

      Log::error('paystack processing error: ' . $e->getMessage());

      return $this->errorResponse(
        status: false,
        message: 'OHHHH!!!!  ::: Something went wrong  during payment processing, pls try again later or report the issue to the customer support!',
        statusCode: 400,
       
        );

      
    }

  }


  public function checkTransactionRef(Request $request)
  {
      $transactionRef = $request->query('trxref');

      $checkTrxRef = Transaction::where('transaction_reference', $transactionRef)->first();

      if($checkTrxRef) {
                
        $response = $this->paymentService->verifyTransaction($transactionRef);

        return $this->paymentService->handlePaystackPaymentResponse($response, $transactionRef);

    }else {

      return $this->errorResponse(
        status: false,
        message: 'Invalid Transaction Reference',
        statusCode: 400,
       
        );

     
    }


  }

  
 

}