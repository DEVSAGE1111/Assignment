<?php

use App\Livewire\FormCreator;
use App\Models\AiSetting;
use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use App\Services\AiFormGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('dashboard page renders successfully', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/');
    $response->assertStatus(200);
});

test('ai form generator fallback creates realistic form', function () {
    $schema = AiFormGenerator::generate('Create a job recruitment form');

    expect($schema)->toBeArray();
    expect($schema['title'])->toContain('Mock AI');
    expect($schema['fields'])->not->toBeEmpty();
});

test('api can fetch form structure', function () {
    $form = Form::create([
        'title' => 'API Test Form',
        'description' => 'A form to test api endpoint.',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
        ],
    ]);

    $response = $this->getJson("/api/forms/{$form->uuid}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.title', 'API Test Form');
});

test('api can submit valid response', function () {
    $form = Form::create([
        'title' => 'API Submission Form',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
            ['id' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => false],
        ],
    ]);

    $response = $this->postJson("/api/forms/{$form->uuid}/submit", [
        'data' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success');

    expect(Submission::where('form_id', $form->id)->count())->toBe(1);
});

test('api rejects invalid response based on rules', function () {
    $form = Form::create([
        'title' => 'API Invalid Form',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
            ['id' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
        ],
    ]);

    // Missing fields and invalid email format
    $response = $this->postJson("/api/forms/{$form->uuid}/submit", [
        'data' => [
            'email' => 'not-an-email',
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonStructure(['errors' => ['data.name', 'data.email']]);
});

test('api submissions endpoint is secured', function () {
    $form = Form::create([
        'title' => 'Secure Form',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
        ],
    ]);

    // 1. Without Token
    $response = $this->getJson("/api/forms/{$form->uuid}/submissions");
    $response->assertStatus(401);

    // 2. With Invalid Token
    $response = $this->getJson("/api/forms/{$form->uuid}/submissions", [
        'X-API-Key' => 'wrong_token',
    ]);
    $response->assertStatus(401);

    // 3. With Valid Token
    $token = env('API_SECRET_TOKEN', 'formify_secret_token_12345');
    $response = $this->getJson("/api/forms/{$form->uuid}/submissions", [
        'X-API-Key' => $token,
    ]);
    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

test('AI generation shows genuine error on failure', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => ['message' => 'API Key expired'],
        ], 403),
    ]);

    AiSetting::create([
        'enabled' => true,
        'active_provider' => 'gemini',
        'provider_settings' => [
            'gemini' => ['api_key' => 'expired_key', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
        ],
    ]);

    Livewire::test(FormCreator::class)
        ->set('aiPrompt', 'Create a simple contact form')
        ->call('generateFromAi')
        ->assertHasNoErrors()
        ->assertSet('errorMessage', 'Failed to generate form: Gemini API Error: API Key expired');
});
