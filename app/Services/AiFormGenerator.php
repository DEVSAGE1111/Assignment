<?php

namespace App\Services;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;

class AiFormGenerator
{
    /**
     * Generate a form schema from a text prompt.
     */
    public static function generate(string $prompt): array
    {
        @set_time_limit(600);

        $setting = AiSetting::first();

        // If settings don't exist, are disabled, or have no provider settings, fallback to Mock AI
        if (! $setting || ! $setting->enabled || empty($setting->provider_settings)) {
            return self::mockGenerate($prompt);
        }

        $provider = $setting->active_provider;
        $provSettings = $setting->provider_settings[$provider] ?? [];

        $systemPrompt = "You are an expert form designer. Create a form based on the user's request.
You must respond with ONLY a valid JSON object matching the following structure. Do NOT wrap it in markdown block or backticks (no ```json). Just return the raw JSON.
{
  \"title\": \"Form Title\",
  \"description\": \"Brief Form Description\",
  \"fields\": [
    {
      \"id\": \"field_id_slug\",
      \"type\": \"text|number|email|select|radio|checkbox|textarea\",
      \"label\": \"Field Label\",
      \"placeholder\": \"Field placeholder or null\",
      \"required\": true,
      \"options\": [\"Option 1\", \"Option 2\"]
    }
  ]
}
Note: Options should only be provided for select or radio types, otherwise set options to null. Make sure 'id' is a clean lowercase alphanumeric slug (e.g., first_name, email_address).";

        $fullPrompt = $systemPrompt."\n\nUser Request: ".$prompt;

        // 1. Google Gemini
        if ($provider === 'gemini' && ! empty($provSettings['api_key'])) {
            $key = $provSettings['api_key'];
            $model = ! empty($provSettings['custom_model']) ? $provSettings['custom_model'] : ($provSettings['model'] ?? 'gemini-1.5-flash');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".$key, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text') ?? '';
                $decoded = json_decode(trim($text), true);
                if ($decoded && isset($decoded['title'], $decoded['fields'])) {
                    return $decoded;
                }
                throw new \Exception('Gemini response is not valid JSON format or missing required keys: '.substr($text, 0, 150));
            }

            $errorMsg = $response->json('error.message') ?? 'Unknown error status: '.$response->status();
            throw new \Exception('Gemini API Error: '.$errorMsg);
        }

        // 2. ChatGPT (OpenAI)
        if ($provider === 'openai' && ! empty($provSettings['api_key'])) {
            $key = $provSettings['api_key'];
            $model = ! empty($provSettings['custom_model']) ? $provSettings['custom_model'] : ($provSettings['model'] ?? 'gpt-4o-mini');

            $response = Http::withToken($key)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $fullPrompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->successful()) {
                $text = $response->json('choices.0.message.content') ?? '';
                $decoded = json_decode(trim($text), true);
                if ($decoded && isset($decoded['title'], $decoded['fields'])) {
                    return $decoded;
                }
                throw new \Exception('OpenAI response is not valid JSON format.');
            }

            $errorMsg = $response->json('error.message') ?? 'Unknown error status: '.$response->status();
            throw new \Exception('OpenAI API Error: '.$errorMsg);
        }

        // 3. X (xAI Grok)
        if ($provider === 'xai' && ! empty($provSettings['api_key'])) {
            $key = $provSettings['api_key'];
            $model = ! empty($provSettings['custom_model']) ? $provSettings['custom_model'] : ($provSettings['model'] ?? 'grok-2-1212');

            $response = Http::withToken($key)
                ->post('https://api.x.ai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $fullPrompt],
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json('choices.0.message.content') ?? '';
                $textClean = preg_replace('/^```(?:json)?|```$/i', '', trim($text));
                $decoded = json_decode(trim($textClean), true);
                if ($decoded && isset($decoded['title'], $decoded['fields'])) {
                    return $decoded;
                }
                throw new \Exception('Grok response is not valid JSON format.');
            }

            $errorMsg = $response->json('error.message') ?? 'Unknown error status: '.$response->status();
            throw new \Exception('Grok API Error: '.$errorMsg);
        }

        // 4. Ollama (Local AI)
        if ($provider === 'ollama' && ! empty($provSettings['url'])) {
            $url = rtrim($provSettings['url'], '/');
            $model = ! empty($provSettings['custom_model']) ? $provSettings['custom_model'] : ($provSettings['model'] ?? 'llama3');

            $request = Http::timeout(600);
            if (! empty($provSettings['api_key'])) {
                $request->withToken($provSettings['api_key']);
            }

            $response = $request->post($url.'/api/chat', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $fullPrompt],
                ],
                'stream' => false,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $text = $response->json('message.content') ?? '';
                $decoded = json_decode(trim($text), true);
                if ($decoded && isset($decoded['title'], $decoded['fields'])) {
                    return $decoded;
                }
                throw new \Exception('Ollama response is not valid JSON format.');
            }

            throw new \Exception('Ollama API Error status: '.$response->status());
        }

        // Default Fallback (if settings exist but not fully configured yet)
        return self::mockGenerate($prompt);
    }

    /**
     * Test connection to the selected provider.
     */
    public static function testConnection(string $provider, array $settings): array
    {
        try {
            if ($provider === 'gemini') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    return ['success' => false, 'message' => 'API Key is required.'];
                }
                $model = ! empty($settings['custom_model']) ? $settings['custom_model'] : ($settings['model'] ?? 'gemini-1.5-flash');

                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".$key, [
                    'contents' => [['parts' => [['text' => 'Ping']]]],
                ]);

                if ($response->successful()) {
                    return ['success' => true, 'message' => 'Connection successful! Gemini API is responsive.'];
                }
                $error = $response->json('error.message') ?? 'Unknown error';

                return ['success' => false, 'message' => 'Failed: '.$error];
            }

            if ($provider === 'openai') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    return ['success' => false, 'message' => 'API Key is required.'];
                }

                $response = Http::withToken($key)->get('https://api.openai.com/v1/models');
                if ($response->successful()) {
                    return ['success' => true, 'message' => 'Connection successful! OpenAI credentials are valid.'];
                }
                $error = $response->json('error.message') ?? 'Unknown error';

                return ['success' => false, 'message' => 'Failed: '.$error];
            }

            if ($provider === 'xai') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    return ['success' => false, 'message' => 'API Key is required.'];
                }

                // Grok models list
                $response = Http::withToken($key)->get('https://api.x.ai/v1/models');
                if ($response->successful()) {
                    return ['success' => true, 'message' => 'Connection successful! Grok API is responsive.'];
                }
                $error = $response->json('error.message') ?? 'Unknown error';

                return ['success' => false, 'message' => 'Failed: '.$error];
            }

            if ($provider === 'ollama') {
                $url = rtrim($settings['url'] ?? 'http://localhost:11434', '/');
                if (empty($url)) {
                    return ['success' => false, 'message' => 'Ollama URL is required.'];
                }

                $request = Http::timeout(3);
                if (! empty($settings['api_key'])) {
                    $request->withToken($settings['api_key']);
                }

                $response = $request->get($url.'/api/tags');
                if ($response->successful()) {
                    return ['success' => true, 'message' => 'Connection successful! Ollama server is running and accessible.'];
                }

                return ['success' => false, 'message' => 'Failed: Ollama server did not respond correctly. Verify URL.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Connection failed: '.$e->getMessage()];
        }

        return ['success' => false, 'message' => 'Invalid active provider selected.'];
    }

    /**
     * Fetch available models from the selected provider.
     *
     * @throws \Exception
     */
    public static function fetchModels(string $provider, array $settings): array
    {
        try {
            if ($provider === 'gemini') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    throw new \Exception('API Key is required to fetch Gemini models.');
                }
                $response = Http::get('https://generativelanguage.googleapis.com/v1beta/models?key='.$key);
                if ($response->successful()) {
                    $models = [];
                    foreach ($response->json('models') ?? [] as $m) {
                        $name = str_replace('models/', '', $m['name']);
                        if (str_starts_with($name, 'gemini')) {
                            $models[] = [
                                'value' => $name,
                                'label' => $m['displayName'] ?? $name,
                            ];
                        }
                    }
                    if (empty($models)) {
                        throw new \Exception('No Gemini models found.');
                    }

                    return $models;
                }
                $error = $response->json('error.message') ?? 'Gemini API returned error.';
                throw new \Exception('Failed: '.$error);
            }

            if ($provider === 'openai') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    throw new \Exception('API Key is required to fetch OpenAI models.');
                }
                $response = Http::withToken($key)->get('https://api.openai.com/v1/models');
                if ($response->successful()) {
                    $models = [];
                    foreach ($response->json('data') ?? [] as $m) {
                        $id = $m['id'];
                        if (str_starts_with($id, 'gpt') || str_starts_with($id, 'o1') || str_starts_with($id, 'o3')) {
                            $models[] = [
                                'value' => $id,
                                'label' => $id,
                            ];
                        }
                    }
                    usort($models, fn ($a, $b) => strcmp($a['label'], $b['label']));
                    if (empty($models)) {
                        throw new \Exception('No ChatGPT models found.');
                    }

                    return $models;
                }
                $error = $response->json('error.message') ?? 'OpenAI API returned error.';
                throw new \Exception('Failed: '.$error);
            }

            if ($provider === 'xai') {
                $key = $settings['api_key'] ?? '';
                if (empty($key)) {
                    throw new \Exception('API Key is required to fetch Grok models.');
                }
                $response = Http::withToken($key)->get('https://api.x.ai/v1/models');
                if ($response->successful()) {
                    $models = [];
                    foreach ($response->json('data') ?? [] as $m) {
                        $id = $m['id'];
                        $models[] = [
                            'value' => $id,
                            'label' => $id,
                        ];
                    }
                    if (empty($models)) {
                        throw new \Exception('No Grok models found.');
                    }

                    return $models;
                }
                $error = $response->json('error.message') ?? 'Grok API returned error.';
                throw new \Exception('Failed: '.$error);
            }

            if ($provider === 'ollama') {
                $url = rtrim($settings['url'] ?? 'http://localhost:11434', '/');
                if (empty($url)) {
                    throw new \Exception('Ollama URL is required to fetch models.');
                }

                $request = Http::timeout(3);
                if (! empty($settings['api_key'])) {
                    $request->withToken($settings['api_key']);
                }

                $response = $request->get($url.'/api/tags');
                if ($response->successful()) {
                    $models = [];
                    foreach ($response->json('models') ?? [] as $m) {
                        $name = $m['name'];
                        $models[] = [
                            'value' => str_replace(':latest', '', $name),
                            'label' => $name,
                        ];
                    }
                    if (empty($models)) {
                        throw new \Exception('No Ollama models found.');
                    }

                    return $models;
                }
                throw new \Exception('Failed: Ollama server did not respond correctly.');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        throw new \Exception('Invalid active provider selected.');
    }

    /**
     * Fallback mock generator that inspects the prompt keywords to return realistic forms.
     */
    private static function mockGenerate(string $prompt): array
    {
        $promptLower = strtolower($prompt);

        if (str_contains($promptLower, 'contact') || str_contains($promptLower, 'inquiry')) {
            return [
                'title' => 'Contact Us Form (Mock AI)',
                'description' => 'Send us a message and we will get back to you shortly.',
                'fields' => [
                    [
                        'id' => 'full_name',
                        'type' => 'text',
                        'label' => 'Full Name',
                        'placeholder' => 'Enter your full name',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'email',
                        'type' => 'email',
                        'label' => 'Email Address',
                        'placeholder' => 'Enter your email address',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'subject',
                        'type' => 'text',
                        'label' => 'Subject',
                        'placeholder' => 'What is this regarding?',
                        'required' => false,
                        'options' => null,
                    ],
                    [
                        'id' => 'category',
                        'type' => 'select',
                        'label' => 'Category',
                        'placeholder' => 'Select a category',
                        'required' => true,
                        'options' => ['General support', 'Sales inquiry', 'Billing question', 'Technical issues'],
                    ],
                    [
                        'id' => 'message',
                        'type' => 'textarea',
                        'label' => 'Your Message',
                        'placeholder' => 'Write your message here...',
                        'required' => true,
                        'options' => null,
                    ],
                ],
            ];
        }

        if (str_contains($promptLower, 'feedback') || str_contains($promptLower, 'survey') || str_contains($promptLower, 'review')) {
            return [
                'title' => 'Customer Feedback Survey (Mock AI)',
                'description' => 'We value your input! Tell us about your recent experience.',
                'fields' => [
                    [
                        'id' => 'product_name',
                        'type' => 'text',
                        'label' => 'Product / Service Name',
                        'placeholder' => 'e.g., Cloud Portal V2',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'rating',
                        'type' => 'radio',
                        'label' => 'Overall Satisfaction',
                        'placeholder' => null,
                        'required' => true,
                        'options' => ['Very satisfied', 'Satisfied', 'Neutral', 'Unsatisfied', 'Very unsatisfied'],
                    ],
                    [
                        'id' => 'comments',
                        'type' => 'textarea',
                        'label' => 'What can we improve?',
                        'placeholder' => 'Describe your experience...',
                        'required' => false,
                        'options' => null,
                    ],
                    [
                        'id' => 'recommend',
                        'type' => 'checkbox',
                        'label' => 'I would recommend this product to a colleague',
                        'placeholder' => null,
                        'required' => false,
                        'options' => null,
                    ],
                ],
            ];
        }

        if (str_contains($promptLower, 'event') || str_contains($promptLower, 'registration') || str_contains($promptLower, 'rsvp')) {
            return [
                'title' => 'Event Registration Form (Mock AI)',
                'description' => 'Sign up to secure your spot at our upcoming conference.',
                'fields' => [
                    [
                        'id' => 'attendee_name',
                        'type' => 'text',
                        'label' => 'Attendee Full Name',
                        'placeholder' => 'John Doe',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'email',
                        'type' => 'email',
                        'label' => 'Email Address',
                        'placeholder' => 'john.doe@example.com',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'ticket_type',
                        'type' => 'select',
                        'label' => 'Ticket Type',
                        'placeholder' => 'Choose a pass',
                        'required' => true,
                        'options' => ['General Admission ($50)', 'VIP Access ($150)', 'Virtual Pass (Free)'],
                    ],
                    [
                        'id' => 'dietary_restrictions',
                        'type' => 'textarea',
                        'label' => 'Dietary Requirements',
                        'placeholder' => 'None, Vegetarian, Vegan, Gluten-free, etc.',
                        'required' => false,
                        'options' => null,
                    ],
                ],
            ];
        }

        if (str_contains($promptLower, 'newsletter') || str_contains($promptLower, 'subscribe') || str_contains($promptLower, 'signup')) {
            return [
                'title' => 'Newsletter Subscription (Mock AI)',
                'description' => 'Stay updated with our latest news and monthly insights.',
                'fields' => [
                    [
                        'id' => 'email',
                        'type' => 'email',
                        'label' => 'Email Address',
                        'placeholder' => 'you@example.com',
                        'required' => true,
                        'options' => null,
                    ],
                    [
                        'id' => 'frequency',
                        'type' => 'radio',
                        'label' => 'Email Frequency',
                        'placeholder' => null,
                        'required' => true,
                        'options' => ['Daily digest', 'Weekly round-up', 'Monthly newsletter'],
                    ],
                    [
                        'id' => 'terms_agreement',
                        'type' => 'checkbox',
                        'label' => 'I agree to the privacy policy and terms of service',
                        'placeholder' => null,
                        'required' => true,
                        'options' => null,
                    ],
                ],
            ];
        }

        // Generic Default Mock Form
        return [
            'title' => 'AI Generated Dynamic Form (Mock AI)',
            'description' => 'A custom generated form matching your prompt: "'.$prompt.'".',
            'fields' => [
                [
                    'id' => 'input_name',
                    'type' => 'text',
                    'label' => 'Full Name',
                    'placeholder' => 'John Doe',
                    'required' => true,
                    'options' => null,
                ],
                [
                    'id' => 'input_email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'john.doe@example.com',
                    'required' => true,
                    'options' => null,
                ],
                [
                    'id' => 'input_rating',
                    'type' => 'select',
                    'label' => 'Rating',
                    'placeholder' => 'Rate this generated form',
                    'required' => true,
                    'options' => ['Good', 'Average', 'Bad'],
                ],
                [
                    'id' => 'input_description',
                    'type' => 'textarea',
                    'label' => 'Additional Comments',
                    'placeholder' => 'Write your feedback...',
                    'required' => false,
                    'options' => null,
                ],
            ],
        ];
    }
}
