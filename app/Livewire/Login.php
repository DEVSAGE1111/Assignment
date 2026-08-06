<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public string $errorMessage = '';

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected array $messages = [
        'email.required' => 'Please enter your email address.',
        'email.email' => 'Please enter a valid email address.',
        'password.required' => 'Please enter your password.',
        'password.min' => 'Password must be at least 6 characters.',
    ];

    public function login()
    {
        $this->validate();
        $this->errorMessage = '';

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            return redirect()->intended(route('ai-settings'));
        }

        $this->errorMessage = 'These credentials do not match our records.';
    }

    public function render()
    {
        return view('livewire.login')
            ->layout('components.layouts.app', ['title' => 'Login - Formify AI']);
    }
}
