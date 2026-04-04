<?php

namespace Tests\Feature;

use App\Models\PendingApproval;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_register_fails_with_invalid_invite_code(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new.user@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'invite_code' => 'BAD-CODE',
        ]);

        $response->assertSessionHasErrors('invite_code');
    }

    public function test_register_with_valid_invite_sets_pending_and_creates_approval(): void
    {
        $member = User::factory()->create([
            'member_id' => 'SIPR26-TS-1001',
            'email' => 'placeholder@sipr.com',
            'status' => 'active',
            'google_id' => null,
        ]);

        $response = $this->post('/register', [
            'name' => 'Registered Member',
            'email' => 'registered.member@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'invite_code' => 'SIPR26-TS-1001',
        ]);

        $response->assertRedirect('/login');

        $member->refresh();

        $this->assertSame('pending', $member->status);
        $this->assertSame('registered.member@example.com', $member->email);
        $this->assertDatabaseHas('pending_approvals', [
            'user_id' => $member->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'pending.user@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'pending',
        ]);

        $response = $this->post('/login', [
            'email' => 'pending.user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_approve_pending_registration(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'status' => 'pending',
        ]);

        $approval = PendingApproval::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.approvals.approve', $approval));

        $response->assertRedirect();
        $this->assertSame('approved', $approval->fresh()->status);
        $this->assertSame('active', $user->fresh()->status);
    }

    public function test_active_user_can_login_and_access_dashboard(): void
    {
        User::factory()->create([
            'email' => 'active.user@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $login = $this->post('/login', [
            'email' => 'active.user@example.com',
            'password' => 'secret123',
        ]);

        $login->assertRedirect('/dashboard');

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk();
    }

    public function test_forgot_password_sends_reset_link_to_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset.me@example.com',
            'status' => 'active',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset.token@example.com',
            'password' => Hash::make('old-secret'),
            'status' => 'active',
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
    }

    public function test_google_redirect_routes_user_to_provider(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('/google-oauth'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('/google-oauth');
    }

    public function test_google_callback_logs_in_matching_user(): void
    {
        $user = User::factory()->create([
            'email' => 'google.user@example.com',
            'status' => 'active',
            'google_id' => null,
            'google_email' => null,
        ]);

        $googleUser = new class () {
            public function getId(): string
            {
                return 'google-123';
            }

            public function getEmail(): string
            {
                return 'google.user@example.com';
            }

            public function getName(): string
            {
                return 'Google User';
            }
        };

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-123',
            'google_email' => 'google.user@example.com',
        ]);
    }
}
