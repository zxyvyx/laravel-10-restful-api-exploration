<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotNull;

class UserTest extends TestCase
{
    public function testRegisterSuccess(): void
    {
        $this->post('/api/users', [
            'username' => 'user_test',
            'password' => 'secret:)',
            'name' => 'user_test'
        ])->assertStatus(201)->assertJson([
            "data" => [
                "username" => "user_test",
                "name" => "user_test"
            ]
        ]);
    }

    public function testRegisterFailed(): void
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "username" => [
                    "The username field is required."
                ],
                "password" => [
                    "The password field is required."
                ],
                "name" => [
                    "The name field is required."
                ]
            ]
        ]);
    }

    public function testRegisterUsernameAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'user_test',
            'password' => 'secret:)',
            'name' => 'user_test'
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "username" => [
                    "The username has already been taken."
                ],
            ]
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test',
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test"
            ]
        ]);
        $user = User::where('username', 'test')->first();
        assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'alex',
            'password' => 'test',
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "Incorrect password or username"
                ]
            ]
        ]);
    }

    public function testLoginFailedPasswordNotFound()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'alex',
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "Incorrect password or username"
                ]
            ]
        ]);
    }
}
