<?php

use App\Livewire\AiSettings;
use App\Models\AiSetting;
use App\Models\User;
use App\Services\AiFormGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('settings page renders successfully', function () {
    $user = User::factory()->create();
    AiSetting::create([
        'enabled' => true,
        'active_provider' => 'gemini',
        'provider_settings' => [
            'gemini' => ['api_key' => 'mock_key', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
        ],
    ]);

    $response = $this->actingAs($user)->get('/ai-settings');
    $response->assertStatus(200);
});

test('can save settings through Livewire component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    AiSetting::create([
        'enabled' => true,
        'active_provider' => 'gemini',
        'provider_settings' => [
            'gemini' => ['api_key' => 'key_a', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
        ],
    ]);

    Livewire::test(AiSettings::class)
        ->set('enabled', true)
        ->set('activeProvider', 'openai')
        ->set('providerSettings.openai.api_key', 'key_b')
        ->set('providerSettings.openai.model', 'gpt-4o-mini')
        ->call('saveSettings')
        ->assertHasNoErrors();

    $setting = AiSetting::first();
    expect($setting->active_provider)->toBe('openai');
    expect($setting->provider_settings['openai']['api_key'])->toBe('key_b');
});

test('testConnection returns success on valid api ping', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Pong']]]]]], 200),
        'api.openai.com/*' => Http::response(['object' => 'list', 'data' => []], 200),
    ]);

    $geminiResult = AiFormGenerator::testConnection('gemini', ['api_key' => 'valid_key']);
    expect($geminiResult['success'])->toBeTrue();

    $openaiResult = AiFormGenerator::testConnection('openai', ['api_key' => 'valid_key']);
    expect($openaiResult['success'])->toBeTrue();
});

test('testConnection returns false on failed api ping', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'API key not valid']], 400),
        'api.openai.com/*' => Http::response(['error' => ['message' => 'Invalid API key']], 401),
    ]);

    $geminiResult = AiFormGenerator::testConnection('gemini', ['api_key' => 'invalid_key']);
    expect($geminiResult['success'])->toBeFalse();
    expect($geminiResult['message'])->toContain('API key not valid');

    $openaiResult = AiFormGenerator::testConnection('openai', ['api_key' => 'invalid_key']);
    expect($openaiResult['success'])->toBeFalse();
    expect($openaiResult['message'])->toContain('Invalid API key');
});

test('fetchModels retrieves options successfully', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'models' => [
                ['name' => 'models/gemini-1.5-flash', 'displayName' => 'Gemini 1.5 Flash'],
                ['name' => 'models/gemini-1.5-pro', 'displayName' => 'Gemini 1.5 Pro'],
            ],
        ], 200),
    ]);

    $fetched = AiFormGenerator::fetchModels('gemini', ['api_key' => 'valid_key']);
    expect($fetched)->toHaveCount(2);
    expect($fetched[0]['value'])->toBe('gemini-1.5-flash');
    expect($fetched[0]['label'])->toBe('Gemini 1.5 Flash');
});

test('Livewire fetchModelsFromApi updates availableModels list', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'models' => [
                ['name' => 'models/gemini-1.5-flash', 'displayName' => 'Gemini 1.5 Flash'],
                ['name' => 'models/gemini-1.5-pro', 'displayName' => 'Gemini 1.5 Pro'],
            ],
        ], 200),
    ]);

    AiSetting::create([
        'enabled' => true,
        'active_provider' => 'gemini',
        'provider_settings' => [
            'gemini' => ['api_key' => 'mock_key', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
        ],
    ]);

    Livewire::test(AiSettings::class)
        ->set('activeProvider', 'gemini')
        ->set('providerSettings.gemini.api_key', 'mock_key')
        ->call('fetchModelsFromApi')
        ->assertHasNoErrors()
        ->assertSet('connectionSuccess', true);
});
