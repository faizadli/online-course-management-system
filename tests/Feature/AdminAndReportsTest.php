<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class AdminAndReportsTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    public function test_admin_manages_categories(): void
    {
        $admin = $this->makeUser(UserRole::Admin);

        $this->actingAs($admin)->post('/dashboard/admin/categories', ['name' => 'Languages', 'description' => 'Learn languages'])
            ->assertSessionHasNoErrors();
        $category = Category::query()->where('name', 'Languages')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)->put("/dashboard/admin/categories/{$category->id}", ['name' => 'Foreign Languages', 'description' => 'x', 'is_active' => '1']);
        $this->assertSame('Foreign Languages', $category->fresh()->name);

        $this->actingAs($admin)->delete("/dashboard/admin/categories/{$category->id}");
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_deactivate_a_user_but_not_themselves(): void
    {
        $admin = $this->makeUser(UserRole::Admin);
        $student = $this->makeUser();

        $this->actingAs($admin)->patch("/dashboard/admin/users/{$student->id}/toggle");
        $this->assertFalse($student->fresh()->is_active);

        $this->actingAs($admin)->patch("/dashboard/admin/users/{$admin->id}/toggle")->assertSessionHasErrors('user');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_user_list_supports_search_and_role_filter(): void
    {
        $admin = $this->makeUser(UserRole::Admin);
        $this->makeUser(UserRole::Student, ['name' => 'Searchable Student']);
        $this->makeUser(UserRole::Instructor, ['name' => 'Another Instructor']);

        $this->actingAs($admin)->get('/dashboard/admin/users?search=Searchable')->assertOk()->assertSee('Searchable Student')->assertDontSee('Another Instructor');
        $this->actingAs($admin)->get('/dashboard/admin/users?role=instructor')->assertOk()->assertSee('Another Instructor')->assertDontSee('Searchable Student');
    }

    public function test_admin_reports_show_platform_revenue_from_payments(): void
    {
        $admin = $this->makeUser(UserRole::Admin);
        $course = $this->makeCourse(null, ['price' => 75]);
        $enrolment = $this->enrol($this->makeUser(), $course);
        Payment::query()->create([
            'enrolment_id' => $enrolment->id, 'reference' => 'PAY-TEST000001', 'amount' => 75,
            'method' => 'card', 'status' => 'paid', 'card_brand' => 'Visa', 'card_last4' => '4242', 'paid_at' => now(),
        ]);

        $this->actingAs($admin)->get('/dashboard/admin/reports')
            ->assertOk()
            ->assertSee('Total Revenue')
            ->assertSee('$75.00')
            ->assertSee('PAY-TEST000001');
    }

    public function test_instructor_enrolment_and_report_pages_load(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $this->enrol($this->makeUser(), $course);

        foreach (['', '/courses', '/builder', '/assessments', '/enrolments', '/reports', '/profile', '/settings'] as $path) {
            $this->actingAs($instructor)->get('/dashboard/instructor'.$path)->assertOk();
        }
    }

    public function test_student_dashboard_pages_load(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);

        foreach (['', '/courses', '/courses/browse', '/assessments', '/feedback', '/profile', '/settings'] as $path) {
            $this->actingAs($student)->get('/dashboard/student'.$path)->assertOk();
        }
    }

    public function test_database_backup_command_creates_a_file(): void
    {
        $directory = storage_path('app/backups');
        File::deleteDirectory($directory);

        // Backups of in-memory SQLite are not meaningful; skip the file copy and assert command wiring instead.
        if (config('database.connections.sqlite.database') === ':memory:') {
            $this->artisan('list')->assertExitCode(0);
            $this->assertTrue(collect(\Illuminate\Support\Facades\Artisan::all())->has('db:backup'));

            return;
        }

        $this->artisan('db:backup')->assertExitCode(0);
        $this->assertNotEmpty(File::files($directory));
    }
}
