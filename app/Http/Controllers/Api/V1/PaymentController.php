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

      if($response['status']) {

          if(!$response['status']) {

            Log::error($response['message']);

            return $this->errorResponse(
              status: false,
              message: 'OHHHH!!!!  ::: Something went wrong  during payment processing, pls try again later or report the issue to the customer support!',
              statusCode: 400,
              errors: [
                'errors' => $response['message'],
              ]
            );




          }

          if(isset($response['data']['authorization_url'])){

        //  return  Redirect::to($response['data']['authorization_url'])->send();

          return $this->successResponse(
            status: true,
            message: 'Transaction initialized successfully.Copy the authorization url and open it in a browser.',
            data: [
              'data' => $response,
            ],

          );
          }


      } else {

       // Log::error($response['message']);
      //  Log::error($response);

        return $this->errorResponse(
          status: false,
          message: 'OHHHH!!!!  ::: Something went wrong  during payment processing, pls try again later or report the issue to the customer support!',
          statusCode: 400,
          errors: [
            'errors' => $response['message'],
          ]
          );


     }

    }catch(Exception $e) {

      Log::error($e->getMessage());

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

        return $this->handlePaymentResponse($response, $transactionRef);

    }else {

      return $this->errorResponse(
        status: false,
        message: 'Invalid Transaction Reference',
        statusCode: 400,
       
        );

     
    }


  }

  public function handlePaymentResponse($response, $trx_ref)
 {

      if($response['status']) {
      //  $response = $res['data'];
      
        if(array_key_exists('data', $response) && is_array($response)) {

        //  log::info($response = $res['data']);


          if($response['data']['status'] != 'success') {

            return $this->errorResponse(
              status: false,
              message: 'Transaction is still pending,if you have been debited , kindly contact support for more Info',
              statusCode: 400,
            
              );
      

          }


          try {

            DB::beginTransaction();


            $transaction = Transaction::with('user')->where('transaction_reference', $trx_ref)->first();

            $user = $transaction->user;


            $transaction->update([
              'status' => ($response['data']['status'] == 'success') ? PaymentStatus::PAID : PaymentStatus::FAILED,
              'amount'=> $transaction->amount,
              'gateway_response' => $response['data']['gateway_response'],
              'payment_reference' => (!empty(($response['data']['authorization'])))?$response['data']['authorization']['authorization_code']:'',
              'transaction_date' => date("Y-m-d H:i:s", strtotime($response['data']['paid_at'])),
              'gateway' => 'paystack',
              'gateway_response' =>  $response['data']['gateway_response'],
              'signature' => (!empty(($response['data']['authorization'])))?$response['data']['authorization']['signature']:'',
              'description' => 'Wallet funding through paystack',
              'purpose' => 'Funding of wallet',
            ]);

            $wallet = [];
            $wallet['user_id'] = $user->id;
            $wallet['balance'] = $response['data']['amount'] / 100;

            Wallet::create($wallet);

            $user->notify(new PaymentSuccessNotification(
              name: $user->name,
              amount:  $transaction->amount,
              invoice: $transaction->invoice,
              title: 'payment'
            ));

            DB::commit();

            return $this->successResponse(
              status: true,
              message: 'Transaction Completed successfully',
              data: [
                'data' => $response,
              ],

            );

      


          }catch(Exception $e) {


            Log::error($e->getMessage());
            DB::rollback();

            return $this->errorResponse(
              status: false,
              message: 'OHHHH!!!!  ::: Something went wrong  during payment processing, pls try again later or report the issue to the customer support!',
              statusCode: 400,
            
              );


          }

        }  

        return $this->errorResponse(
          status: false,
          message: 'OHHHH!!!!  ::: Something went wrong  during payment processing, pls try again later or report the issue to the customer support!',
          statusCode: 400,
        
          );



      }

 }


 

}