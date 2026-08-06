<?php

namespace App\Livewire;

use App\Models\AiSetting;
use App\Models\Form;
use App\Services\AiFormGenerator;
use App\Services\DocumentParser;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormCreator extends Component
{
    use WithFileUploads;

    // Form configurations
    public string $title = '';

    public string $description = '';

    public array $fields = [];

    // Generator inputs
    public string $generationMode = 'manual'; // manual, ai, import

    public string $aiPrompt = '';

    // File upload
    public $uploadedFile;

    // Interactive states
    public bool $isGenerating = false;

    public string $errorMessage = '';

    public function mount(): void
    {
        // Add a default text field to start with
        $this->addField();
    }

    public function addField(): void
    {
        $this->fields[] = [
            'id' => 'field_'.Str::random(6),
            'type' => 'text',
            'label' => 'New Field',
            'placeholder' => '',
            'required' => false,
            'options' => null,
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    public function moveFieldUp(int $index): void
    {
        if ($index > 0) {
            $temp = $this->fields[$index];
            $this->fields[$index] = $this->fields[$index - 1];
            $this->fields[$index - 1] = $temp;
        }
    }

    public function moveFieldDown(int $index): void
    {
        if ($index < count($this->fields) - 1) {
            $temp = $this->fields[$index];
            $this->fields[$index] = $this->fields[$index + 1];
            $this->fields[$index + 1] = $temp;
        }
    }

    public function changeFieldType(int $index, string $type): void
    {
        $this->fields[$index]['type'] = $type;
        if (in_array($type, ['select', 'radio'])) {
            if (empty($this->fields[$index]['options'])) {
                $this->fields[$index]['options'] = ['Option 1', 'Option 2'];
            }
        } else {
            $this->fields[$index]['options'] = null;
        }
    }

    public function addOption(int $index): void
    {
        if (is_array($this->fields[$index]['options'])) {
            $this->fields[$index]['options'][] = 'New Option';
        } else {
            $this->fields[$index]['options'] = ['New Option'];
        }
    }

    public function removeOption(int $index, int $optIndex): void
    {
        if (is_array($this->fields[$index]['options'])) {
            unset($this->fields[$index]['options'][$optIndex]);
            $this->fields[$index]['options'] = array_values($this->fields[$index]['options']);
        }
    }

    public function generateFromAi(): void
    {
        $this->validate([
            'aiPrompt' => 'required|min:5',
        ], [
            'aiPrompt.required' => 'Please enter a description for the form you want to generate.',
            'aiPrompt.min' => 'Please provide a more detailed prompt (at least 5 characters).',
        ]);

        $this->isGenerating = true;
        $this->errorMessage = '';

        try {
            $schema = AiFormGenerator::generate($this->aiPrompt);

            $this->title = $schema['title'] ?? 'Generated Form';
            $this->description = $schema['description'] ?? '';
            $this->fields = [];

            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as $f) {
                    $this->fields[] = [
                        'id' => $f['id'] ?? ('field_'.Str::random(6)),
                        'type' => $f['type'] ?? 'text',
                        'label' => $f['label'] ?? 'Field Name',
                        'placeholder' => $f['placeholder'] ?? '',
                        'required' => (bool) ($f['required'] ?? false),
                        'options' => $f['options'] ?? null,
                    ];
                }
            }

            $setting = AiSetting::first();
            $aiEnabled = false;
            $providerName = '';
            if ($setting && $setting->enabled) {
                $provider = $setting->active_provider;
                $provSettings = $setting->provider_settings[$provider] ?? [];
                if ($provider === 'ollama') {
                    $aiEnabled = ! empty($provSettings['url']);
                    $providerName = 'Ollama';
                } else {
                    $aiEnabled = ! empty($provSettings['api_key']);
                    $providerName = match ($provider) {
                        'gemini' => 'Google Gemini',
                        'openai' => 'ChatGPT (OpenAI)',
                        'xai' => 'X (xAI/Grok)',
                        default => ucfirst($provider)
                    };
                }
            }

            // Switch to manual mode so they can review/edit the generated form
            $this->generationMode = 'manual';

            if ($aiEnabled) {
                session()->flash('message', "Form generated successfully via {$providerName}! Review the fields below before saving.");
            } else {
                session()->flash('warning', '⚠️ Notice: No AI API credentials configured. Generated form using local Mock AI fallback. Please configure settings in AI Settings for real AI generation.');
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to generate form: '.$e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function importFromFile(): void
    {
        $this->validate([
            'uploadedFile' => 'required|file|max:5120', // Max 5MB
        ]);

        $this->isGenerating = true;
        $this->errorMessage = '';

        try {
            $filePath = $this->uploadedFile->getRealPath();
            $extension = strtolower($this->uploadedFile->getClientOriginalExtension());

            if ($extension === 'docx') {
                // Word document import: Extract text and ask AI to parse it
                $text = DocumentParser::parseDocx($filePath);
                if (empty($text)) {
                    throw new \Exception('The Word file contains no readable text or is corrupted.');
                }

                // Prompt AI to convert the extracted document text into a form schema
                $aiPrompt = 'Extract the questions, options, and input requirements from the following document text and convert them into a form schema: '.substr($text, 0, 3000);
                $schema = AiFormGenerator::generate($aiPrompt);

                $this->title = $schema['title'] ?? 'Imported Word Form';
                $this->description = $schema['description'] ?? 'Generated from uploaded Word document.';
                $this->fields = [];

                if (isset($schema['fields']) && is_array($schema['fields'])) {
                    foreach ($schema['fields'] as $f) {
                        $this->fields[] = [
                            'id' => $f['id'] ?? ('field_'.Str::random(6)),
                            'type' => $f['type'] ?? 'text',
                            'label' => $f['label'] ?? 'Field Name',
                            'placeholder' => $f['placeholder'] ?? '',
                            'required' => (bool) ($f['required'] ?? false),
                            'options' => $f['options'] ?? null,
                        ];
                    }
                }

                $this->generationMode = 'manual';
                session()->flash('message', 'Parsed Word document and generated form! Review below.');
            } elseif ($extension === 'xlsx' || $extension === 'csv') {
                // Excel/CSV import: Read the layout mapping (Label, Type, Required, Options, Placeholder)
                $rows = ($extension === 'xlsx')
                    ? DocumentParser::parseXlsx($filePath)
                    : DocumentParser::parseCsv($filePath);

                if (count($rows) <= 1) {
                    throw new \Exception('The file is empty or contains only headers.');
                }

                // Headers mapping: we expect headers like Label, Type, Required, Options, Placeholder
                $headers = array_map('strtolower', $rows[0]);

                $labelIdx = array_search('label', $headers);
                $typeIdx = array_search('type', $headers);
                $requiredIdx = array_search('required', $headers);
                $placeholderIdx = array_search('placeholder', $headers);
                $optionsIdx = array_search('options', $headers);

                if ($labelIdx === false) {
                    // Fallback to absolute indices if headers don't match
                    $labelIdx = 0;
                    $typeIdx = 1;
                    $requiredIdx = 2;
                    $optionsIdx = 3;
                    $placeholderIdx = 4;
                }

                $this->title = 'Imported Spreadsheet Form';
                $this->description = 'Form fields imported from '.$this->uploadedFile->getClientOriginalName();
                $this->fields = [];

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty($row) || empty($row[$labelIdx])) {
                        continue;
                    }

                    $label = $row[$labelIdx];
                    $type = strtolower($row[$typeIdx] ?? 'text');
                    $reqStr = strtolower($row[$requiredIdx] ?? 'false');
                    $required = in_array($reqStr, ['yes', 'true', '1', 'required']);
                    $placeholder = $row[$placeholderIdx] ?? '';

                    $options = null;
                    if (! empty($row[$optionsIdx]) && in_array($type, ['select', 'radio'])) {
                        // Options can be comma-separated
                        $options = array_map('trim', explode(',', $row[$optionsIdx]));
                    }

                    // Enforce valid types
                    $validTypes = ['text', 'number', 'email', 'select', 'radio', 'checkbox', 'textarea'];
                    if (! in_array($type, $validTypes)) {
                        $type = 'text';
                    }

                    $this->fields[] = [
                        'id' => Str::slug($label, '_').'_'.Str::random(3),
                        'type' => $type,
                        'label' => $label,
                        'placeholder' => $placeholder,
                        'required' => $required,
                        'options' => $options,
                    ];
                }

                $this->generationMode = 'manual';
                session()->flash('message', 'Successfully imported form fields from spreadsheet!');
            } else {
                throw new \Exception('Unsupported file format. Please upload .xlsx, .csv, or .docx.');
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Error importing file: '.$e->getMessage();
        } finally {
            $this->isGenerating = false;
            $this->uploadedFile = null;
        }
    }

    public function saveForm()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string',
        ], [
            'title.required' => 'Form title is required.',
            'fields.required' => 'Add at least one field to save the form.',
            'fields.*.label.required' => 'Each field must have a label.',
        ]);

        // Clean slugs
        foreach ($this->fields as $index => $field) {
            $cleanSlug = Str::slug($field['label'], '_');
            if (empty($cleanSlug)) {
                $cleanSlug = 'field_'.$index;
            }
            // Ensure unique slugs
            $this->fields[$index]['id'] = $cleanSlug;
        }

        $form = Form::create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'fields' => $this->fields,
        ]);

        session()->flash('message', 'Form "'.$form->title.'" has been created successfully.');

        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.form-creator')
            ->layout('components.layouts.app', ['title' => 'Create Form - Formify AI']);
    }
}
