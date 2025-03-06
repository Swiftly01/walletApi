<?php

namespace App\Enums;

enum PaymentDesc: string {
  case PURPOSE_AIRTIME = 'airtime';
  case TITLE_PURCHASE = 'purchase';
  case DESCRIPTION = 'airtime purchase via VTU';
  case PURPOSE = 'airtime purchase';
  case PAYSTACK_DESCRIPTION = 'Wallet funding through paystack';
  case PAYSTACK_PURPOSE = 'Funding of wallet';
  
  
}