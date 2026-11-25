<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo accounts — every account uses the password "password".
     */
    public const ACCOUNTS = [
        ['email' => 'admin@example.com', 'name' => 'Admin User', 'role' => UserRole::Admin],
        ['email' => 'instructor@example.com', 'name' => 'Sarah Chen', 'role' => UserRole::Instructor, 'expertise' => 'UI/UX Design, Figma', 'bio' => 'Senior product designer with 10+ years of experience teaching design fundamentals.'],
        ['email' => 'john@example.com', 'name' => 'John Doe', 'role' => UserRole::Instructor, 'expertise' => 'Web Development, Data Science', 'bio' => 'Lead software engineer who has taught over 15,000 students to build real-world web and data applications.'],
        ['email' => 'emily@example.com', 'name' => 'Emily Rose', 'role' => UserRole::Instructor, 'expertise' => 'Digital Marketing, Finance', 'bio' => 'Marketing strategist helping small businesses grow with data-driven campaigns.'],
        ['email' => 'alex@example.com', 'name' => 'Alex Kim', 'role' => UserRole::Instructor, 'expertise' => 'Photography, Communication', 'bio' => 'Professional photographer and communication coach focused on practical, confidence-building skills.'],
        ['email' => 'student@example.com', 'name' => 'Ahmad Rizki', 'role' => UserRole::Student],
        ['email' => 'lisa@example.com', 'name' => 'Lisa Park', 'role' => UserRole::Student],
        ['email' => 'david@example.com', 'name' => 'David Chen', 'role' => UserRole::Student],
        ['email' => 'maya@example.com', 'name' => 'Maya Putri', 'role' => UserRole::Student],
        ['email' => 'budi@example.com', 'name' => 'Budi Santoso', 'role' => UserRole::Student],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                    'expertise' => $data['expertise'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        $this->call([
            CategorySeeder::class,
            CourseSeeder::class,
            LearningActivitySeeder::class,
        ]);
    }
}
