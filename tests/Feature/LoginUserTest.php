<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginUserTest extends TestCase
{    

    use RefreshDatabase;

    public function test_authenticated_user_can_login_with_correct_credentials()
    {
         //create test user in the database
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);


        //send login request

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        //Assertions

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email']
            ]
        ]);





    }


    public function test_login_fails_with_wrong_password()
    {

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password1234',
        ]);

        $response->assertStatus(403)->assertJson([
            'status' => false,
            'message' => 'Invalid Email or Password',

        ]);


    }

    public function test_login_fails_with_missing_fields()
    {
         $response = $this->postJson('/api/login', []);
          
         $response->assertStatus(422)->assertJson([
            'status' => false,
            'message' => 'validation error', 
          ])->assertJsonStructure([
            'errors' => ['email', 'password']

          ]);
    }
   




}
