<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.allowed_emails' => ['felix@example.com']]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_allowed_email_creates_the_user_and_logs_in(): void
    {
        $this->freezeTime();
        $this->fakeGoogleUser('Felix@Example.com');

        $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'felix@example.com',
            'google_id' => 'google-123',
            'email_verified_at' => now(),
        ]);
    }

    public function test_existing_user_is_reused_not_duplicated(): void
    {
        $this->travelTo('2026-11-23 10:00:00');
        $user = User::factory()->create(['email' => 'felix@example.com']);
        $originalVerificationTime = $user->email_verified_at->toISOString();
        $this->travel(1)->day();
        $this->fakeGoogleUser('felix@example.com');

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($originalVerificationTime, $user->refresh()->email_verified_at->toISOString());
    }

    public function test_email_outside_the_allowlist_is_rejected(): void
    {
        $this->fakeGoogleUser('intruso@example.com');

        $this->get(route('auth.google.callback'))->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->fakeGoogleUser('felix@example.com', emailVerified: false);

        $this->get(route('auth.google.callback'))->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_verified_email_timestamp_cannot_be_changed(): void
    {
        $this->travelTo('2026-11-23 10:00:00');
        $user = User::factory()->create();
        $originalVerificationTime = $user->email_verified_at->toISOString();
        $this->travel(1)->day();

        $user->email_verified_at = now();
        $user->save();

        $this->assertSame($originalVerificationTime, $user->refresh()->email_verified_at->toISOString());
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function fakeGoogleUser(string $email, bool $emailVerified = true): void
    {
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-123',
            'name' => 'Felix',
            'email' => $email,
            'email_verified' => $emailVerified,
        ]));
    }
}
