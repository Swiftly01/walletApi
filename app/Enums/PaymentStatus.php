<?php

namespace App\Enums;

enum PaymentStatus: int {
  case PENDING = 0;
  case PAID = 1;
  case FAILED = -1;
}