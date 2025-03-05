<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_reference' => $this->transaction_reference,
            'invoice' => $this->invoice,
            'description' => $this->status,
            'purpose' => $this->purpose,
            'gateway' => $this->gateway,
            'gateway_response' => $this->gateway_response,
            'payment_reference' => $this->payment_reference,
            'signature' => $this->signature,
            'transaction_date' => $this->transaction_date,  
        ];
    }
}
