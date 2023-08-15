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

    public function testGetCurrentUserSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test",
            ]
        ]);
    }

    public function testGetCurrentUserUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current')->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "Unauthorized"
                ],
            ]
        ]);
    }

    public function testGetCurrentUserInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'wrong_token_test'
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "Unauthorized"
                ],
            ]
        ]);
    }

    public function testUpdateCurrentUserPasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'password' => 'password_baru'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test",
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateCurrentUserNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'name' => 'name_baru'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "name_baru",
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateCurrentUserAllDataSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'name' => 'name_baru',
            'password' => 'password_baru'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                "username" => "test",
                "name" => "name_baru",
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateCurrentUserNameFailed()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'name' => 'this_is_more_than_100_characters_abcdefghijklmnopqrstuvwxyzhdjashdjhsajdjshdjasdjhskkskdjsakdkdasdsadsaddjk'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "name" => [
                    "The name field must not be greater than 100 characters."
                ],
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateCurrentUserPasswordFailed()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'password' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            "errors" => [
                "message" => [
                    "Name or password is required"
                ],
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateCurrentUserFailedUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'password' => 'new_password'
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "Unauthorized"
                ],
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertEquals($oldUser->name, $newUser->name);
    }
}
