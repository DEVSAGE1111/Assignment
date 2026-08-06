<?php

namespace App\Livewire;

use App\Models\AiSetting;
use App\Services\AiFormGenerator;
use Livewire\Component;

class AiSettings extends Component
{
    public bool $enabled = true;

    public string $activeProvider = 'gemini';

    public array $providerSettings = [];

    public array $availableModels = [];

    public bool $isFetchingModels = false;

    // Key visibility toggles
    public array $showKeys = [
        'gemini' => false,
        'openai' => false,
        'xai' => false,
        'ollama' => false,
    ];

    // Connection testing statuses
    public string $connectionStatus = '';

    public bool $connectionSuccess = false;

    public bool $isValidating = false;

    public function mount(): void
    {
        $setting = AiSetting::first();

        if ($setting) {
            $this->enabled = $setting->enabled;
            $this->activeProvider = $setting->active_provider;
            $this->providerSettings = $setting->provider_settings;
        } else {
            // Default scaffolding
            $this->providerSettings = [
                'gemini' => ['api_key' => '', 'model' => 'gemini-1.5-flash', 'custom_model' => ''],
                'openai' => ['api_key' => '', 'model' => 'gpt-4o-mini', 'custom_model' => ''],
                'xai' => ['api_key' => '', 'model' => 'grok-2-1212', 'custom_model' => ''],
                'ollama' => ['url' => 'http://localhost:11434', 'api_key' => '', 'model' => 'llama3', 'custom_model' => ''],
            ];
        }

        $this->loadDefaultModelsList($this->activeProvider);
    }

    public function updatedActiveProvider($value): void
    {
        $this->loadDefaultModelsList($value);
        $this->connectionStatus = '';
    }

    public function loadDefaultModelsList(string $provider): void
    {
        $defaults = [
            'gemini' => [
                ['value' => 'gemini-1.5-flash', 'label' => 'Gemini 1.5 Flash (Recommended)'],
                ['value' => 'gemini-1.5-pro', 'label' => 'Gemini 1.5 Pro'],
                ['value' => 'gemini-2.0-flash-exp', 'label' => 'Gemini 2.0 Flash Exp'],
            ],
            'openai' => [
                ['value' => 'gpt-4o-mini', 'label' => 'GPT-4o Mini (Recommended)'],
                ['value' => 'gpt-4o', 'label' => 'GPT-4o'],
                ['value' => 'gpt-4-turbo', 'label' => 'GPT-4 Turbo'],
                ['value' => 'gpt-3.5-turbo', 'label' => 'GPT-3.5 Turbo'],
            ],
            'xai' => [
                ['value' => 'grok-2', 'label' => 'Grok 2 (Recommended)'],
                ['value' => 'grok-2-mini', 'label' => 'Grok 2 Mini'],
                ['value' => 'grok-beta', 'label' => 'Grok Beta'],
            ],
            'ollama' => [
                ['value' => 'llama3', 'label' => 'Llama 3'],
                ['value' => 'mistral', 'label' => 'Mistral'],
                ['value' => 'phi3', 'label' => 'Phi 3'],
                ['value' => 'gemma2', 'label' => 'Gemma 2'],
            ],
        ];

        $this->availableModels = $defaults[$provider] ?? [];
    }

    public function fetchModelsFromApi(): void
    {
        $this->connectionStatus = '';
        $this->isFetchingModels = true;

        $settings = $this->providerSettings[$this->activeProvider] ?? [];

        if ($this->activeProvider === 'ollama' && empty($settings['url'])) {
            $this->connectionStatus = 'Failed to fetch models: Ollama URL is required.';
            $this->connectionSuccess = false;
            $this->isFetchingModels = false;

            return;
        }

        if ($this->activeProvider !== 'ollama' && empty($settings['api_key'])) {
            $this->connectionStatus = 'Failed to fetch models: API Key is required.';
            $this->connectionSuccess = false;
            $this->isFetchingModels = false;

            return;
        }

        try {
            $fetched = AiFormGenerator::fetchModels($this->activeProvider, $settings);

            $this->availableModels = $fetched;

            $currentModel = $this->providerSettings[$this->activeProvider]['model'] ?? '';
            $inList = collect($fetched)->contains('value', $currentModel);
            if (! $inList && ! empty($fetched)) {
                $this->providerSettings[$this->activeProvider]['model'] = $fetched[0]['value'];
            }

            $this->connectionSuccess = true;
            $this->connectionStatus = 'Successfully loaded '.count($fetched).' models dynamically from the provider API!';
        } catch (\Exception $e) {
            $this->connectionSuccess = false;
            $this->connectionStatus = 'Failed to fetch models: '.$e->getMessage();
        } finally {
            $this->isFetchingModels = false;
        }
    }

    public function toggleKeyVisibility(string $provider): void
    {
        $this->showKeys[$provider] = ! ($this->showKeys[$provider] ?? false);
    }

    public function checkConnection(): void
    {
        $this->connectionStatus = '';
        $this->isValidating = true;

        $settings = $this->providerSettings[$this->activeProvider] ?? [];

        if ($this->activeProvider === 'ollama' && empty($settings['url'])) {
            $this->connectionStatus = 'Failed: Ollama URL is required.';
            $this->connectionSuccess = false;
            $this->isValidating = false;

            return;
        }

        if ($this->activeProvider !== 'ollama' && empty($settings['api_key'])) {
            $this->connectionStatus = 'Failed: API Key is required to test connection.';
            $this->connectionSuccess = false;
            $this->isValidating = false;

            return;
        }

        $result = AiFormGenerator::testConnection($this->activeProvider, $settings);

        $this->connectionSuccess = $result['success'];
        $this->connectionStatus = $result['message'];
        $this->isValidating = false;
    }

    public function saveSettings()
    {
        $rules = [
            'activeProvider' => 'required|in:gemini,openai,xai,ollama',
        ];

        if ($this->enabled) {
            if ($this->activeProvider === 'ollama') {
                $rules['providerSettings.ollama.url'] = 'required|url';
            } else {
                $rules["providerSettings.{$this->activeProvider}.api_key"] = 'required|string';
            }
        }

        $this->validate($rules, [
            'providerSettings.ollama.url.required' => 'Ollama URL is required.',
            'providerSettings.ollama.url.url' => 'Ollama URL must be a valid URL.',
            'providerSettings.*.api_key.required' => 'API Key is required when AI features are enabled.',
        ]);

        $setting = AiSetting::first() ?: new AiSetting;
        $setting->enabled = $this->enabled;
        $setting->active_provider = $this->activeProvider;
        $setting->provider_settings = $this->providerSettings;
        $setting->save();

        session()->flash('message', 'AI settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.ai-settings')
            ->layout('components.layouts.app', ['title' => 'AI Settings - Formify AI']);
    }
}
