<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    public function test_each_role_logs_in_and_lands_on_its_own_dashboard(): void
    {
        foreach ([
            [UserRole::Student, 'dashboard.student.index'],
            [UserRole::Instructor, 'dashboard.instructor.index'],
            [UserRole::Admin, 'dashboard.admin.index'],
        ] as [$role, $route]) {
            $user = $this->makeUser($role);

            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route($route));

            $this->post('/logout');
        }
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->makeUser();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = $this->makeUser(UserRole::Student, ['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_student_can_register(): void
    {
        $this->post('/register/student', [
            'name' => 'New Student',
            'email' => 'new.student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect();

        $user = User::query()->where('email', 'new.student@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertNotSame('Password123!', $user->password, 'Password must be stored hashed.');
    }

    public function test_instructor_can_register_with_expertise(): void
    {
        $this->post('/register/instructor', [
            'name' => 'New Instructor',
            'email' => 'new.instructor@example.com',
            'expertise' => 'Data Science',
            'bio' => 'Ten years of experience.',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect();

        $user = User::query()->where('email', 'new.instructor@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Instructor, $user->role);
        $this->assertSame('Data Science', $user->expertise);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $existing = $this->makeUser();

        $this->post('/register/student', [
            'name' => 'Dup',
            'email' => $existing->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_role_based_access_control_blocks_other_dashboards(): void
    {
        $student = $this->makeUser(UserRole::Student);

        $this->actingAs($student)->get('/dashboard/admin')->assertForbidden();
        $this->actingAs($student)->get('/dashboard/instructor')->assertForbidden();
        $this->actingAs($student)->get('/dashboard/student')->assertOk();
    }

    public function test_guests_are_redirected_to_login_for_dashboards(): void
    {
        $this->get('/dashboard/student')->assertRedirect(route('login'));
        $this->get('/dashboard/instructor/builder')->assertRedirect(route('login'));
    }
}
