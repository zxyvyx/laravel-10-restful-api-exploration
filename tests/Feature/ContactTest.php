<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateContactSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@doe.com',
            'phone' => '08123456789',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@doe.com',
                'phone' => '08123456789',
            ]
        ]);
    }

    public function testCreateContactFailedBadRequest()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'firstName' => '',
            'lastName' => 'Doe',
            'email' => 'john',
            'phone' => '0812323233232422243456789',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'firstName' => [
                    "The first name field is required."
                ],
                'email' => [
                    "The email field must be a valid email address."
                ],
                'phone' => [
                    "The phone field must not be greater than 20 characters."
                ],
            ]
        ]);
    }

    public function testCreateContactFailedUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@doe.com',
            'phone' => '08123456789',
        ], [
            'Authorization' => 'example-wrong-token'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    "Unauthorized"
                ],
            ]
        ]);
    }
}
