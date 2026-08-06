<?php

use App\Livewire\AiSettings;
use App\Livewire\ApiDocViewer;
use App\Livewire\FormCreator;
use App\Livewire\FormDashboard;
use App\Livewire\FormSubmissions;
use App\Livewire\FormViewer;
use App\Livewire\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', FormDashboard::class)->name('home');
    Route::get('/forms/create', FormCreator::class)->name('forms.create');
    Route::get('/forms/{uuid}/submissions', FormSubmissions::class)->name('forms.submissions');
    Route::get('/api-docs', ApiDocViewer::class)->name('api-docs');
    Route::get('/ai-settings', AiSettings::class)->name('ai-settings');
});

// Public form response view
Route::get('/forms/{uuid}', FormViewer::class)->name('forms.show');

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');
