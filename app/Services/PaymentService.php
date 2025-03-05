<?php

namespace App\Services;

use App\Models\Transaction;
use Exception;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PaymentService 
{
   public function trxRef(string $prefix = 'TXN')
  {
      // Generate a random 4-digit number
      $part1 = rand(1000, 9999);
  
      // Generate a random alphanumeric string (4 characters, uppercase)
      $part2 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
  
      // Generate another random 4-digit number
      $part3 = rand(1000, 9999);
  
      // Generate a random alphanumeric string (3 characters, uppercase)
      $part4 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));
  
      // Combine all parts
      return $prefix.'-' . $part1 . '-' . $part2 . '-' . $part3 . '-' . $part4;

  }


public  function get_actual_charges($amount_to_be_paid)
{  
  if($amount_to_be_paid <= 2500){

    $amount = $amount_to_be_paid/(1-(1.5/100)) +0.03;

   

  } else if($amount_to_be_paid > 2500){

    $amount = $amount_to_be_paid/(1-(1.5/100)) +100;

  }

  $convinience= $amount - $amount_to_be_paid;

    $convinienceFees =  ceil($convinience);

  
  if($convinienceFees > 2000){

    $Charges = 2000;

    $convinienceFees = $Charges;
   }

  $total_charges=   $convinienceFees;

  return $total_charges;



}

public function storeInitTransactions($user, $amount, $txref, $inv )
{

    try {
      
      $userInvoice = UserService::getUserInvoice($user);

      Transaction::create([
        'user_id' => $user->id,
        'amount'  => $amount,
        'status' => PaymentStatus::PENDING,
        'transaction_reference' => $txref,
        'invoice' => $userInvoice ? $userInvoice->invoice : $inv,          
      ]);


    }catch(Exception $err) {

       Log::error('Unable to store initial transaction details' . ' ' . $err->getMessage());

       throw new Exception($err->getMessage());

    }
}



public function initPaystackTransaction($user, $amount, $tx_ref, $secret_key, $init_url, $callback_url)
{
  
  
  $fields = [
    'email' => $user->email,
    'amount' => $amount * 100,
    'reference' =>  $tx_ref,
    'callback_url' => $callback_url,
  ];

  $fields_string = http_build_query($fields);

  //open connection
  $ch = curl_init();
  
  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $init_url);
  curl_setopt($ch,CURLOPT_POST, true);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $secret_key,
    "Cache-Control: no-cache",
  ));
  
  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
  
  //execute post
  $result = curl_exec($ch);

  $response = json_decode($result, true);
  
  Log::info($response);

  return $response;

}


public function verifyTransaction(string $trx_ref)
{
  $curl = curl_init();
        
  curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/verify/$trx_ref",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer " . config('services.secret_key.key'),
      "Cache-Control: no-cache",
      ),
  ));
  
  $responses = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  $response = json_decode($responses, true);

  //log::info($response);

  return $response;

}


public function handleAirtimePayment($wallet, $amount,  bool $response = false):bool
 {
      //Success payment simulated

      try{
          

        if($response) {

          return true;

        }


        return false;

         

      } catch(Exception $e) {

        Log::error('Airtime purchase error' . $e->getMessage());


        return false;

      }



 }





}