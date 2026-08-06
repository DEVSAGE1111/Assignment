<?php

use App\Livewire\Login;
use App\Models\AiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected from settings page to login', function () {
    $response = $this->get('/ai-settings');
    $response->assertRedirect('/login');
});

test('login page renders successfully', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('submitting invalid credentials displays error message', function () {
    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'wrong_password')
        ->call('login')
        ->assertSet('errorMessage', 'These credentials do not match our records.');

    $this->assertGuest();
});

test('submitting valid credentials authenticates user and redirects', function () {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    AiSetting::create([
        'enabled' => true,
        'active_provider' => 'gemini',
        'provider_settings' => [
            'gemini' => ['api_key' => 'mock_key', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
        ],
    ]);

    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('ai-settings'));

    $this->assertAuthenticated();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');
    $response->assertRedirect('/');

    $this->assertGuest();
});
