<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckWalletBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_wallet_can_check_balance()
    {

            //create a user with a wallet
            $user = User::factory()->create();

           // Log::info($user);

            //create a wallet for the user
         $wallet =  Wallet::create([
                'user_id' => $user->id,
                'balance' => 1000.00,


            ]);

          //  Log::info($wallet);

            //authenticate user
         $sanctum =  Sanctum::actingAs($user);

       //  Log::info('sanctum'. $sanctum);
            
            $response = $this->getJson('/api/v1/wallet/balance');

            $response->assertStatus(200)
                     ->assertJson([
                        'status' => true,
                        'message' => 'user details and wallet balance fetched successfully',
                     ])
                     ->assertJsonStructure([
                        'data' => [
                            'id',
                            'name',
                            'email',
                            'wallet_details' => [
                                'id',
                                'user_id',
                                'balance',
                            ]
                        ]
                     ]);




    }


    public function test_authenticated_user_without_wallet_receives_error_message()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/wallet/balance');

        $response->assertStatus(404)->assertJson([
            'status' => false,
            'message' => "Sorry, You don't have an active wallet, kindly fund your account to activate your wallet"

        ]);
    }


    public function test_unauthenticated_user_cannot_access_wallet_balance()
    {
         $response = $this->getJson('/api/v1/wallet/balance');

         $response->assertStatus(401);
    }

    
}
