<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
}
