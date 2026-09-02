<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::query()->where('role', UserRole::Instructor)->first();

        $courses = [
            ['title' => 'UI/UX Design Fundamentals', 'category' => 'design-creative', 'price' => 49, 'status' => 'published', 'enrolments' => 324],
            ['title' => 'Full Stack Web Development', 'category' => 'tech-programming', 'price' => 79, 'status' => 'published', 'enrolments' => 512],
            ['title' => 'Digital Marketing Mastery', 'category' => 'business-marketing', 'price' => 59, 'status' => 'published', 'enrolments' => 186],
            ['title' => 'Python Data Science Bootcamp', 'category' => 'tech-programming', 'price' => 89, 'status' => 'published', 'enrolments' => 278],
            ['title' => 'Brand Identity Design', 'category' => 'design-creative', 'price' => 44, 'status' => 'draft', 'enrolments' => 0],
            ['title' => 'Public Speaking Confidence', 'category' => 'personal-development', 'price' => 29, 'status' => 'published', 'enrolments' => 142],
        ];

        foreach ($courses as $data) {
            $category = Category::query()->where('slug', $data['category'])->first();

            Course::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'category_id' => $category?->id,
                    'instructor_id' => $instructor?->id,
                    'status' => $data['status'],
                    'price' => $data['price'],
                    'level' => 'Beginner',
                    'language' => 'English',
                    'short_description' => 'Learn '.$data['title'].' with hands-on projects.',
                    'enrolments_count' => $data['enrolments'],
                ]
            );
        }
    }
}
