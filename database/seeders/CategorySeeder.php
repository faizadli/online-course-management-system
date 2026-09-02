<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Design & Creative', 'description' => 'UI/UX, graphic design, and creative skills.'],
            ['name' => 'Tech & Programming', 'description' => 'Software development, data science, and IT.'],
            ['name' => 'Business & Marketing', 'description' => 'Business strategy, marketing, and entrepreneurship.'],
            ['name' => 'Personal Development', 'description' => 'Soft skills, productivity, and self-improvement.'],
            ['name' => 'Photography', 'description' => 'Photography techniques and visual storytelling.'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
