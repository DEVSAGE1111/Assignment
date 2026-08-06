<?php

namespace Database\Seeders;

use App\Models\AiSetting;
use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $contactForm = Form::create([
            'title' => 'Contact Us',
            'description' => 'Get in touch with our team for support or inquiries.',
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'text',
                    'label' => 'Full Name',
                    'placeholder' => 'John Doe',
                    'required' => true,
                    'options' => null,
                ],
                [
                    'id' => 'email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'john@example.com',
                    'required' => true,
                    'options' => null,
                ],
                [
                    'id' => 'reason',
                    'type' => 'select',
                    'label' => 'Reason for Contact',
                    'placeholder' => 'Please select a reason',
                    'required' => true,
                    'options' => ['Support', 'Sales', 'General Inquiry'],
                ],
                [
                    'id' => 'message',
                    'type' => 'textarea',
                    'label' => 'Message',
                    'placeholder' => 'How can we help you?',
                    'required' => true,
                    'options' => null,
                ],
            ],
        ]);

        Submission::create([
            'form_id' => $contactForm->id,
            'data' => [
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'reason' => 'Support',
                'message' => 'I have a billing question regarding my last invoice.',
            ],
            'created_at' => now()->subDays(2),
        ]);

        Submission::create([
            'form_id' => $contactForm->id,
            'data' => [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'reason' => 'Sales',
                'message' => 'I would like to request a demo of the enterprise features.',
            ],
            'created_at' => now()->subDay(),
        ]);

        $feedbackForm = Form::create([
            'title' => 'Product Feedback Survey',
            'description' => 'Help us improve our service by sharing your thoughts.',
            'fields' => [
                [
                    'id' => 'product_name',
                    'type' => 'text',
                    'label' => 'Product Name',
                    'placeholder' => 'Enter the product you purchased',
                    'required' => true,
                    'options' => null,
                ],
                [
                    'id' => 'rating',
                    'type' => 'radio',
                    'label' => 'Overall Rating',
                    'placeholder' => null,
                    'required' => true,
                    'options' => ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                ],
                [
                    'id' => 'review',
                    'type' => 'textarea',
                    'label' => 'Detailed Review',
                    'placeholder' => 'What did you like or dislike?',
                    'required' => false,
                    'options' => null,
                ],
                [
                    'id' => 'recommend',
                    'type' => 'checkbox',
                    'label' => 'I would recommend this product to others',
                    'placeholder' => null,
                    'required' => false,
                    'options' => null,
                ],
            ],
        ]);

        Submission::create([
            'form_id' => $feedbackForm->id,
            'data' => [
                'product_name' => 'Formify Pro',
                'rating' => '5 Stars',
                'review' => 'Excellent interface and the AI generator saved me hours of manual input!',
                'recommend' => 'on',
            ],
            'created_at' => now()->subHours(5),
        ]);

        AiSetting::create([
            'enabled' => true,
            'active_provider' => 'gemini',
            'provider_settings' => [
                'gemini' => [
                    'api_key' => env('GEMINI_API_KEY') ?: '',
                    'model' => 'gemini-1.5-flash',
                    'custom_model' => '',
                ],
                'openai' => [
                    'api_key' => env('OPENAI_API_KEY') ?: '',
                    'model' => 'gpt-4o-mini',
                    'custom_model' => '',
                ],
                'xai' => [
                    'api_key' => '',
                    'model' => 'grok-2-1212',
                    'custom_model' => '',
                ],
                'ollama' => [
                    'url' => 'http://localhost:11434',
                    'api_key' => '',
                    'model' => 'llama3',
                    'custom_model' => '',
                ],
            ],
        ]);
    }
}
