<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
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

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'firstName' => 'New John',
            'lastName' => 'New Doe',
            'email' => 'new_mail@mail.com',
            'phone' => '08123456789',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'firstName' => 'New John',
                'lastName' => 'New Doe',
                'email' => 'new_mail@mail.com',
                'phone' => '08123456789',
            ]
        ]);
    }

    public function testUpdateFailedBadRequest()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'lastName' => 'New Doe',
            'email' => 'new_mail@mail.com',
            'phone' => '08123456789',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'firstName' => [
                    'The first name field is required.'
                ],
            ]
        ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $contact->id, [], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteFailedNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . ($contact->id + 1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Contact not found'
                ]
            ]
        ]);
    }

    public function testSearchByFirstNameSuccess()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=first', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByFirstNameFailedNotFound()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=exampleNotFound', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(0, count($response['data']));
        self::assertEquals(0, $response['meta']['total']);
    }

    public function testSearchByLastNameSuccess()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=last', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmailSuccess()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?email=test', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByPhoneSuccess()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?phone=08123456', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchWithPageSuccess()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get('/api/contacts?limit=5&page=2', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(5, count($response['data']));
        self::assertEquals(2, $response['meta']['current_page']);
        self::assertEquals(20, $response['meta']['total']);
    }
}
