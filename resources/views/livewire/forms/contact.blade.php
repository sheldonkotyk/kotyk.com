<?php

use App\Mail\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:100')]
    public string $first_name = '';

    #[Validate('required|string|max:100')]
    public string $last_name = '';

    #[Validate('required|email:rfc|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public string $phone = '';

    #[Validate('required|string|max:5000')]
    public string $comment = '';

    // Honeypot: hidden from people, filled in by bots.
    public string $full_name = '';

    public bool $sent = false;

    public function submit(): void
    {
        $this->validate();

        $key = 'form:contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Too many messages. Please try again in a little while.');

            return;
        }

        RateLimiter::hit($key, 3600);

        if ($this->full_name === '') {
            Mail::to(config('content.forms.contact.to'))->send(new FormSubmission('contact', [
                'First Name' => $this->first_name,
                'Last Name' => $this->last_name,
                'Email' => $this->email,
                'Phone' => $this->phone,
                'Comment' => $this->comment,
            ], $this->email));
        }

        $this->reset();
        $this->sent = true;
    }
};
?>

<div class="not-prose my-8 font-sans">
    @if ($sent)
        <flux:callout icon="check-circle" color="green" heading="Message sent." class="max-w-xl" role="status" />
    @else
        <form wire:submit="submit" class="max-w-xl space-y-6" novalidate>
            @error('form')
                <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" role="alert" />
            @enderror

            <div class="hidden" aria-hidden="true">
                <label for="contact-full-name">Leave this empty</label>
                <input wire:model="full_name" id="contact-full-name" type="text" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="first_name" label="First name" autocomplete="given-name" required />
                <flux:input wire:model="last_name" label="Last name" autocomplete="family-name" required />
            </div>
            <flux:input wire:model="email" type="email" label="Email" placeholder="name@domain.com" autocomplete="email" required />
            <flux:input wire:model="phone" type="tel" label="Phone" badge="Optional" placeholder="555-555-5555" autocomplete="tel" />
            <flux:textarea wire:model="comment" label="Comment, question or request" rows="5" required />
            <flux:button type="submit" variant="primary">Send message</flux:button>
        </form>
    @endif
</div>
