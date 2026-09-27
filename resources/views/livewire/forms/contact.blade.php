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

<div class="container mx-auto">
    @php($input = 'block w-full px-4 py-3 leading-tight border rounded-sm appearance-none focus:outline-hidden focus:bg-white')
    @php($label = 'block mb-2 text-xs font-bold tracking-wide uppercase')
    <div class="mx-6 content md:mx-4">
        @if ($sent)
            <p class="mb-8 font-black text-green-900" role="status">Your message has been successfully sent.</p>
        @else
            <form wire:submit="submit" class="w-full max-w-md" novalidate>
                @if ($errors->any())
                    <div role="alert" class="mb-4">
                        <p>Oops, here's what went wrong:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="hidden" aria-hidden="true">
                    <label for="contact-full-name">Leave this empty</label>
                    <input wire:model="full_name" id="contact-full-name" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="flex flex-wrap mb-4 -mx-3">
                    <div class="w-full px-3 mb-6 md:w-1/2 md:mb-0">
                        <label class="{{ $label }}" for="contact-first-name">First Name</label>
                        <input wire:model="first_name" class="{{ $input }}" id="contact-first-name" type="text" autocomplete="given-name" required>
                    </div>
                    <div class="w-full px-3 md:w-1/2">
                        <label class="{{ $label }}" for="contact-last-name">Last Name</label>
                        <input wire:model="last_name" class="{{ $input }}" id="contact-last-name" type="text" autocomplete="family-name" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="{{ $label }}" for="contact-email">Email</label>
                    <input wire:model="email" class="{{ $input }}" id="contact-email" type="email" placeholder="name@domain.com" autocomplete="email" required>
                </div>
                <div class="mb-4">
                    <label class="{{ $label }}" for="contact-phone">Phone</label>
                    <input wire:model="phone" class="{{ $input }}" id="contact-phone" type="tel" placeholder="555-555-5555" autocomplete="tel">
                </div>
                <div class="mb-4">
                    <label class="{{ $label }}" for="contact-comment">Comment / Question / Request</label>
                    <textarea wire:model="comment" class="h-24 {{ $input }}" id="contact-comment" required></textarea>
                </div>
                <button class="button-primary" type="submit" wire:loading.attr="disabled">Submit</button>
            </form>
        @endif
    </div>
</div>
