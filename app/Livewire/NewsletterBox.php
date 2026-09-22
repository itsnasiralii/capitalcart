<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterBox extends Component
{
    public string $email = '';
    public string $honeypot = ''; // anti-spam bot trap
    public ?string $statusMessage = null;
    public ?string $statusType = null; // success, info, error

    public function subscribe(): void
    {
        // Bot honeypot check
        if (!empty($this->honeypot)) {
            // Silently ignore bot submission without error
            $this->statusMessage = 'Thank you for your interest!';
            $this->statusType = 'success';
            return;
        }

        // Rate limit: 5 attempts per minute per IP/Session
        $throttleKey = 'newsletter:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->statusMessage = "Too many requests. Please try again in {$seconds} seconds.";
            $this->statusType = 'error';
            return;
        }
        RateLimiter::hit($throttleKey, 60);

        // Normalize email
        $cleanEmail = strtolower(trim($this->email));

        $this->validate([
            'email' => 'required|email|max:191',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        try {
            $existing = NewsletterSubscriber::where('email', $cleanEmail)->first();

            if ($existing) {
                if ($existing->status === 'active') {
                    $this->statusMessage = 'You are already subscribed to CapitalCart newsletter updates!';
                    $this->statusType = 'info';
                    $this->email = '';
                    return;
                }

                // Reactivate unsubscribed user
                $existing->update([
                    'status' => 'active',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);

                $this->statusMessage = 'Welcome back! Your newsletter subscription has been reactivated.';
                $this->statusType = 'success';
                $this->email = '';
                return;
            }

            // New subscriber
            NewsletterSubscriber::create([
                'email'         => $cleanEmail,
                'status'        => 'active',
                'subscribed_at' => now(),
                'ip_address'    => request()->ip(),
            ]);

            $this->statusMessage = 'Success! Thank you for subscribing to CapitalCart updates.';
            $this->statusType = 'success';
            $this->email = '';

        } catch (\Throwable $e) {
            $this->statusMessage = 'An unexpected error occurred. Please try again later.';
            $this->statusType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.newsletter-box');
    }
}
