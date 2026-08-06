<?php

namespace App\Livewire;

use Livewire\Component;

class ApiDocViewer extends Component
{
    public string $apiToken = '';

    public function mount(): void
    {
        $this->apiToken = env('API_SECRET_TOKEN', 'formify_secret_token_12345');
    }

    public function render()
    {
        return view('livewire.api-doc-viewer')
            ->layout('components.layouts.app', ['title' => 'API Reference - Formify AI']);
    }
}
