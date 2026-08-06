<?php

use App\Http\Middleware\VerifyApiToken;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

// Public: Get Form Schema
Route::get('/forms/{uuid}', function (string $uuid) {
    $form = Form::where('uuid', $uuid)->first();
    if (! $form) {
        return response()->json([
            'status' => 'error',
            'message' => 'Form not found.',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'title' => $form->title,
            'description' => $form->description,
            'fields' => $form->fields,
        ],
    ]);
});

// Public: Submit Form Response
Route::post('/forms/{uuid}/submit', function (Request $request, string $uuid) {
    $form = Form::where('uuid', $uuid)->first();
    if (! $form) {
        return response()->json([
            'status' => 'error',
            'message' => 'Form not found.',
        ], 404);
    }

    // Build validation rules dynamically based on form schema
    $rules = [];
    $messages = [];
    foreach ($form->fields as $field) {
        $fieldId = $field['id'];
        $fieldKey = 'data.'.$fieldId;
        $fieldRules = [];

        if ($field['required'] ?? false) {
            if ($field['type'] === 'checkbox') {
                $fieldRules[] = 'accepted';
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
        $messages[$fieldKey.'.accepted'] = 'The '.$field['label'].' must be accepted.';
        $messages[$fieldKey.'.email'] = 'The '.$field['label'].' must be a valid email address.';
        $messages[$fieldKey.'.numeric'] = 'The '.$field['label'].' must be a number.';
    }

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    $inputData = $request->input('data', []);

    // Process checkbox values
    $processedData = [];
    foreach ($form->fields as $field) {
        $val = $inputData[$field['id']] ?? null;
        if ($field['type'] === 'checkbox') {
            $processedData[$field['id']] = ($val === true || $val === 'on' || $val === '1' || $val === 'Yes') ? 'Yes' : 'No';
        } else {
            $processedData[$field['id']] = $val;
        }
    }

    $submission = Submission::create([
        'form_id' => $form->id,
        'data' => $processedData,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Response submitted successfully.',
        'submission_id' => $submission->id,
    ], 201);
});

// Authenticated: Get Form Submissions
Route::get('/forms/{uuid}/submissions', function (string $uuid) {
    $form = Form::where('uuid', $uuid)->first();
    if (! $form) {
        return response()->json([
            'status' => 'error',
            'message' => 'Form not found.',
        ], 404);
    }

    $submissions = $form->submissions()->latest()->get()->map(function ($sub) {
        return [
            'submission_id' => $sub->id,
            'submitted_at' => $sub->created_at->toDateTimeString(),
            'data' => $sub->data,
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $submissions,
    ]);
})->middleware(VerifyApiToken::class);
