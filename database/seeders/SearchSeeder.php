<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();
        for ($i = 0; $i < 20; $i++) {
            Contact::create([
                'firstName' => 'test first name' . $i,
                'lastName' => 'test last name' . $i,
                'email' => 'test' . $i . '@test.com',
                'phone' => '08123456' . $i,
                'userId' => $user->id,
            ]);
        }
    }
}
