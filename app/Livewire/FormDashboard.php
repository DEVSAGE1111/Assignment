<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Livewire\Component;

class FormDashboard extends Component
{
    public function deleteForm(int $id): void
    {
        Form::destroy($id);
        session()->flash('message', 'Form deleted successfully.');
    }

    public function render()
    {
        $forms = Form::withCount('submissions')->latest()->get();

        $stats = [
            'total_forms' => Form::count(),
            'total_submissions' => Submission::count(),
            'recent_submissions' => Submission::with('form')->latest()->take(5)->get(),
        ];

        return view('livewire.form-dashboard', compact('forms', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Dashboard - Formify AI']);
    }
}
