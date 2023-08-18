<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
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

    public function testGetContactSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'firstName' => 'test',
                'lastName' => 'test',
                'email' => 'test@mail.com',
                'phone' => '08123456789',
            ]
        ]);
    }

    public function testGetContactFailedNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Contact not found'
                ],
            ]
        ]);
    }

    public function testGetOtherUserContactShouldFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1), [
            'Authorization' => 'test_user_2'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Contact not found'
                ],
            ]
        ]);
    }
}
