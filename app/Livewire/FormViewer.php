<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Livewire\Component;

class FormViewer extends Component
{
    public Form $form;

    public array $data = [];

    public bool $submitted = false;

    public function mount(string $uuid): void
    {
        $this->form = Form::where('uuid', $uuid)->firstOrFail();

        foreach ($this->form->fields as $field) {
            if ($field['type'] === 'checkbox') {
                $this->data[$field['id']] = false;
            } else {
                $this->data[$field['id']] = '';
            }
        }
    }

    public function submit()
    {
        $rules = [];
        $messages = [];

        foreach ($this->form->fields as $field) {
            $fieldId = $field['id'];
            $fieldKey = 'data.'.$fieldId;
            $fieldRules = [];

            if ($field['required'] ?? false) {
                if ($field['type'] === 'checkbox') {
                    $fieldRules[] = 'accepted';
                    $messages[$fieldKey.'.accepted'] = 'The '.$field['label'].' must be accepted.';
                } else {
                    $fieldRules[] = 'required';
                }
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
            }
            if ($field['type'] === 'number') {
                $fieldRules[] = 'numeric';
            }

            $rules[$fieldKey] = $fieldRules;
            $messages[$fieldKey.'.required'] = 'The '.$field['label'].' field is required.';
            $messages[$fieldKey.'.email'] = 'The '.$field['label'].' must be a valid email address.';
            $messages[$fieldKey.'.numeric'] = 'The '.$field['label'].' must be a number.';
        }

        $this->validate($rules, $messages);

        $processedData = $this->data;
        foreach ($this->form->fields as $field) {
            if ($field['type'] === 'checkbox') {
                $processedData[$field['id']] = $processedData[$field['id']] ? 'Yes' : 'No';
            }
        }

        Submission::create([
            'form_id' => $this->form->id,
            'data' => $processedData,
        ]);

        $this->submitted = true;
        session()->flash('message', 'Thank you! Your response has been submitted successfully.');
    }

    public function render()
    {
        return view('livewire.form-viewer')
            ->layout('components.layouts.app', ['title' => $this->form->title.' - Formify AI']);
    }
}
