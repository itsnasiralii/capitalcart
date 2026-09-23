<?php

namespace App\Livewire\Portfolio;

use Livewire\Component;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\RateLimiter;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';
    public string $honeypot = ''; // Spam bot trap
    public bool $isSuccess = false;
    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|min:2|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|min:3|max:200',
            'message' => 'required|string|min:10|max:3000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'    => 'Please provide your full name.',
            'email.required'   => 'A valid email address is required.',
            'email.email'      => 'Please enter a valid email format.',
            'subject.required' => 'Please provide a subject line.',
            'message.required' => 'Please type your message or inquiry.',
            'message.min'      => 'Your message should be at least 10 characters.',
        ];
    }

    public function submit(): void
    {
        // 1. Silent Honeypot Trap
        if (!empty($this->honeypot)) {
            $this->isSuccess = true;
            $this->successMessage = 'Thank you! Your message has been transmitted.';
            $this->reset(['name', 'email', 'subject', 'message', 'honeypot']);
            return;
        }

        // 2. Rate Limiting (5 requests per 10 minutes per IP)
        $throttleKey = 'contact-form:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('rate_limit', "Too many messages sent. Please wait {$seconds} seconds before trying again.");
            return;
        }

        // 3. Validation
        $validated = $this->validate();

        // 4. Persistence
        ContactMessage::create([
            'name'       => strip_tags(trim($validated['name'])),
            'email'      => strtolower(trim($validated['email'])),
            'subject'    => strip_tags(trim($validated['subject'])),
            'message'    => strip_tags(trim($validated['message'])),
            'ip_address' => request()->ip(),
            'is_read'    => false,
        ]);

        RateLimiter::hit($throttleKey, 600);

        // 5. Success state
        $this->isSuccess = true;
        $this->successMessage = 'Thank you, Nasir Ali has received your message and will respond promptly!';
        $this->reset(['name', 'email', 'subject', 'message', 'honeypot']);
    }

    public function render()
    {
        return view('livewire.portfolio.contact-form');
    }
}
