<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_gets_the_timeline_with_shared_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Felix',
            'email' => 'felix@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page): Assert => $page
            ->component('Home', false)
            ->where('auth.user.name', 'Felix')
            ->where('auth.user.email', 'felix@example.com')
        );
    }

    public function test_guest_gets_the_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page): Assert => $page
            ->component('Auth/Login', false)
            ->where('auth.user', null)
        );
    }
}
