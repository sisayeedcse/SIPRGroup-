<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiprMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['member_id' => 'SIPR26-JH-6729', 'name' => 'Jahed Aziz', 'email' => 'jaziro@sipr.com', 'phone' => '01845526909', 'title' => 'President & Founder', 'role' => 'admin', 'locked' => true, 'status' => 'active'],
        ];

        foreach ($members as $member) {
            User::query()->updateOrCreate(
                ['member_id' => $member['member_id']],
                array_merge($member, [
                    'password' => Hash::make('ChangeMe123!'),
                ])
            );
        }
    }
}
