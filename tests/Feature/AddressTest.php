<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function testCreateAddressSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . $contact->id . '/addresses', [
            'street' => 'test street',
            'city' => 'test city',
            'province' => 'test province',
            'country' => 'test country',
            'postalCode' => '12345'
        ], ['Authorization' => 'test'])->assertStatus(201)->assertJson([
            'data' => [
                'street' => 'test street',
                'city' => 'test city',
                'province' => 'test province',
                'country' => 'test country',
                'postalCode' => '12345'
            ]
        ]);
    }

    public function testCreateAddressFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . $contact->id . '/addresses', [
            'street' => 'test street',
            'city' => 'test city',
            'province' => 'test province',
            'country' => '',
            'postalCode' => '12345'
        ], ['Authorization' => 'test'])->assertStatus(400)->assertJson([
            'errors' => [
                'country' => [
                    'The country field is required.'
                ],
            ]
        ]);
    }

    public function testCreateAddressFailedContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . ($contact->id + 1) . '/addresses', [
            'street' => 'test street',
            'city' => 'test city',
            'province' => 'test province',
            'country' => 'test country',
            'postalCode' => '12345'
        ], ['Authorization' => 'test'])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Contact not found'
                ],
            ]
        ]);
    }

    public function testGetAddressSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get('/api/contacts/' . $address->contactId . '/addresses/' . $address->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'street' => 'test street',
                'city' => 'test city',
                'province' => 'test province',
                'country' => 'test country',
                'postalCode' => '12345'
            ]
        ]);
    }

    public function testGetAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get('/api/contacts/' . $address->contactId . '/addresses/' . ($address->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Address not found'
                ],
            ]
        ]);
    }
}
