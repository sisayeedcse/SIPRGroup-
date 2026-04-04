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
            ['member_id' => 'SIPR26-SH-4688', 'name' => 'Shoeb', 'email' => 'shoeb@sipr.com', 'phone' => null, 'title' => 'Treasurer', 'role' => 'finance', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-SA-9514', 'name' => 'Sajid', 'email' => 'sazid@sipr.com', 'phone' => null, 'title' => 'Fund Collector', 'role' => 'member', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-RI-5469', 'name' => 'Rizvi', 'email' => 'rizvi@sipr.com', 'phone' => null, 'title' => 'Accounts Officer', 'role' => 'finance', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-AB-5111', 'name' => 'Abu Tajbit', 'email' => 'ahad@sipr.com', 'phone' => null, 'title' => 'Operations Officer', 'role' => 'secretary', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-AK-2307', 'name' => 'Akib Ahmed', 'email' => 'akib@sipr.com', 'phone' => null, 'title' => 'Research Officer', 'role' => 'member', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-FA-4934', 'name' => 'Fahim', 'email' => 'fahim@sipr.com', 'phone' => null, 'title' => 'Research Officer', 'role' => 'member', 'locked' => false, 'status' => 'active'],
            ['member_id' => 'SIPR26-IM-2838', 'name' => 'Imtiaz', 'email' => 'imtiaz@sipr.com', 'phone' => null, 'title' => 'Research Officer', 'role' => 'member', 'locked' => false, 'status' => 'active'],
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
