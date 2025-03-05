<?php

namespace App\Enums;

enum PaymentDesc: string {
  case PURPOSE_AIRTIME = 'airtime';
  case TITLE_PURCHASE = 'purchase';
  case DESCRIPTION = 'airtime purchase via VTU';
  case PURPOSE = 'airtime purchase';
  
}