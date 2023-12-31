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

    public function testUpdateAddressSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . $address->contactId . '/addresses/' . $address->id, [
            'street' => 'update test street',
            'city' => 'update test city',
            'province' => 'update test province',
            'country' => 'update test country',
            'postalCode' => '54321'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'street' => 'update test street',
                'city' => 'update test city',
                'province' => 'update test province',
                'country' => 'update test country',
                'postalCode' => '54321'
            ]
        ]);
    }

    public function testUpdateAddressFailedBadRequest()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . $address->contactId . '/addresses/' . $address->id, [
            'street' => 'update test street',
            'city' => 'update test city',
            'province' => 'update test province',
            'country' => '',
            'postalCode' => '54321'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'country' => [
                    'The country field is required.'
                ],
            ]
        ]);
    }

    public function testUpdateAddressFailedAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . $address->contactId . '/addresses/' . ($address->id + 1), [
            'street' => 'update test street',
            'city' => 'update test city',
            'province' => 'update test province',
            'country' => 'update test country',
            'postalCode' => '54321'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Address not found'
                ],
            ]
        ]);
    }

    public function testDeleteAddressSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $address->contactId . '/addresses/' . $address->id, [], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteAddressFailedAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $address->contactId . '/addresses/' . ($address->id + 1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Address not found'
                ],
            ]
        ]);
    }

    public function testListContactSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id . '/addresses', [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                [
                    'street' => 'test street',
                    'city' => 'test city',
                    'province' => 'test province',
                    'country' => 'test country',
                    'postalCode' => '12345'
                ]
            ]
        ]);
    }

    public function testListContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1) . '/addresses', [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'Contact not found'
                ],
            ]
        ]);
    }
}
