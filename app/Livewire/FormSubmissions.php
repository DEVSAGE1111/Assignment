<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use App\Services\AiFormGenerator;
use App\Services\DocumentParser;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormSubmissions extends Component
{
    use WithFileUploads;

    public Form $form;

    public string $search = '';

    // File upload
    public $uploadedFile;

    public bool $isImporting = false;

    public string $errorMessage = '';

    public function mount(string $uuid): void
    {
        $this->form = Form::where('uuid', $uuid)->firstOrFail();
    }

    public function deleteSubmission(int $id): void
    {
        Submission::where('form_id', $this->form->id)->where('id', $id)->delete();
        session()->flash('message', 'Submission deleted successfully.');
    }

    public function exportToCsv()
    {
        $submissions = Submission::where('form_id', $this->form->id)->latest()->get();
        $fields = $this->form->fields;

        $callback = function () use ($submissions, $fields) {
            $file = fopen('php://output', 'w');

            // Build CSV Headers
            $headers = ['Submission ID', 'Submitted At'];
            foreach ($fields as $field) {
                $headers[] = $field['label'];
            }
            fputcsv($file, $headers);

            // Build CSV Rows
            foreach ($submissions as $sub) {
                $row = [$sub->id, $sub->created_at->toDateTimeString()];
                foreach ($fields as $field) {
                    $row[] = $sub->data[$field['id']] ?? '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        $fileName = 'submissions_'.$this->form->id.'_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function importResponses(): void
    {
        $this->validate([
            'uploadedFile' => 'required|file|max:5120',
        ]);

        $this->isImporting = true;
        $this->errorMessage = '';

        try {
            $filePath = $this->uploadedFile->getRealPath();
            $extension = strtolower($this->uploadedFile->getClientOriginalExtension());
            $fields = $this->form->fields;

            if ($extension === 'docx') {
                // Parse filled Word questionnaire using AI
                $text = DocumentParser::parseDocx($filePath);
                if (empty($text)) {
                    throw new \Exception('No readable text found in the Word document.');
                }

                // AI System prompt to extract answers
                $fieldsSchemaJson = json_encode($fields);
                $aiPrompt = "You are given a form schema containing the following fields:
{$fieldsSchemaJson}

Based on the text extracted from a filled-out questionnaire document, extract the answers matching each field 'id'.
You must return ONLY a valid JSON object of key-value pairs where key is the field 'id' and value is the parsed answer. Do NOT include markdown backticks (no ```json).

Document Text:
{$text}";

                $schema = AiFormGenerator::generate($aiPrompt);

                // Extract parsed data
                $subData = [];
                foreach ($fields as $field) {
                    $subData[$field['id']] = $schema[$field['id']] ?? $schema['fields'][$field['id']] ?? '';
                }

                Submission::create([
                    'form_id' => $this->form->id,
                    'data' => $subData,
                ]);

                session()->flash('message', 'AI successfully parsed and imported the Word response!');
            } elseif ($extension === 'xlsx' || $extension === 'csv') {
                // Parse Excel/CSV data rows
                $rows = ($extension === 'xlsx')
                    ? DocumentParser::parseXlsx($filePath)
                    : DocumentParser::parseCsv($filePath);

                if (count($rows) <= 1) {
                    throw new \Exception('The spreadsheet is empty or contains no response data.');
                }

                // Headers from the first row of file
                $headers = array_map('trim', $rows[0]);

                // Map headers to field IDs
                // We'll search by field label or field ID
                $headerMap = [];
                foreach ($headers as $colIdx => $header) {
                    $headerLower = strtolower($header);
                    foreach ($fields as $field) {
                        if (strtolower($field['label']) === $headerLower || strtolower($field['id']) === $headerLower) {
                            $headerMap[$field['id']] = $colIdx;
                            break;
                        }
                    }
                }

                if (empty($headerMap)) {
                    throw new \Exception('No headers in the file matched the form field labels/names.');
                }

                $importedCount = 0;
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty($row) || count(array_filter($row)) === 0) {
                        continue;
                    }

                    $subData = [];
                    foreach ($fields as $field) {
                        $colIdx = $headerMap[$field['id']] ?? null;
                        $subData[$field['id']] = ($colIdx !== null && isset($row[$colIdx])) ? $row[$colIdx] : '';
                    }

                    Submission::create([
                        'form_id' => $this->form->id,
                        'data' => $subData,
                    ]);
                    $importedCount++;
                }

                session()->flash('message', "Successfully imported {$importedCount} responses from spreadsheet!");
            } else {
                throw new \Exception('Unsupported file format. Please upload .xlsx, .csv, or .docx.');
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Error importing response: '.$e->getMessage();
        } finally {
            $this->isImporting = false;
            $this->uploadedFile = null;
        }
    }

    public function render()
    {
        $fields = $this->form->fields;

        // Fetch submissions & apply search filter on JSON column
        $query = Submission::where('form_id', $this->form->id);

        if (! empty($this->search)) {
            // In MySQL/SQLite, search the JSON field values using a LIKE query
            $searchString = '%'.$this->search.'%';
            $query->where('data', 'like', $searchString);
        }

        $submissions = $query->latest()->get();

        return view('livewire.form-submissions', compact('submissions', 'fields'))
            ->layout('components.layouts.app', ['title' => 'Submissions - '.$this->form->title]);
    }
}
