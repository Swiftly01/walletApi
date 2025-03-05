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
            'User_details' => $this->whenLoaded('user', function() {
                return new UserResource($this->user);
            }),
            'id' => $this->id,  
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'status' => $this->status ? 'success' : 'failed',
            'transaction_reference' => $this->transaction_reference,
            'invoice' => $this->invoice,
            'description' => $this->status,
            'purpose' => $this->purpose,
            'gateway' => $this->gateway ?? 'Not available for this transaction',
            'gateway_response' => $this->gateway_response ?? 'Not available for this transaction',
            'payment_reference' => $this->payment_reference ?? 'Not available for this transaction',
            'signature' => $this->signature ?? 'Not available for this transaction',
            'transaction_date' => $this->transaction_date,  
        ];
    }
}
