<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();
        Contact::create([
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@mail.com',
            'phone' => '08123456789',
            'userId' => $user->id,
        ]);
    }
}
