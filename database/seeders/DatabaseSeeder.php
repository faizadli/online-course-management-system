<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@skillforge.com',
                'name' => 'Admin User',
                'role' => UserRole::Admin,
            ],
            [
                'email' => 'student@skillforge.com',
                'name' => 'Ahmad Rizki',
                'role' => UserRole::Student,
            ],
            [
                'email' => 'instructor@skillforge.com',
                'name' => 'Sarah Chen',
                'role' => UserRole::Instructor,
                'expertise' => 'UI/UX Design, Figma',
                'bio' => 'Senior product designer with 10+ years of experience teaching design fundamentals.',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'expertise' => $data['expertise'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'is_active' => true,
                ]
            );

            // Always reset password to a fresh bcrypt hash
            $user->password = Hash::make('password');
            $user->save();
        }

        $this->call([
            CategorySeeder::class,
            CourseSeeder::class,
        ]);
    }
}
