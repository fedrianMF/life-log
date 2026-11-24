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
        $this->fakeGoogleUser('Felix@Example.com');

        $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'felix@example.com',
            'google_id' => 'google-123',
        ]);
    }

    public function test_existing_user_is_reused_not_duplicated(): void
    {
        User::factory()->create(['email' => 'felix@example.com']);
        $this->fakeGoogleUser('felix@example.com');

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_email_outside_the_allowlist_is_rejected(): void
    {
        $this->fakeGoogleUser('intruso@example.com');

        $this->get(route('auth.google.callback'))->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function fakeGoogleUser(string $email): void
    {
        Socialite::fake('google', (new GoogleUser)->map([
            'id' => 'google-123',
            'name' => 'Felix',
            'email' => $email,
        ]));
    }
}
