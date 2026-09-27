<?php

use App\Mail\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required_if:notification_method,email|nullable|email:rfc|max:255')]
    public string $email = '';

    #[Validate('required_if:notification_method,sms,whatsapp|nullable|string|max:50')]
    public string $phone = '';

    #[Validate('required|in:email,sms,whatsapp')]
    public string $notification_method = 'email';

    #[Validate('boolean')]
    public bool $livestream_notifications = false;

    // Honeypot: hidden from people, filled in by bots.
    public string $first_name = '';

    public bool $sent = false;

    public function submit(): void
    {
        $this->validate();

        $key = 'form:ds-dispatch:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Too many requests. Please try again in a little while.');

            return;
        }

        RateLimiter::hit($key, 3600);

        if ($this->first_name === '') {
            Mail::to(config('content.forms.ds_dispatch_notifications.to'))->send(new FormSubmission('ds_dispatch_notifications', [
                'Name' => $this->name,
                'Email' => $this->email,
                'Phone' => $this->phone,
                'Notification Method' => $this->notification_method,
                'Livestream Notifications' => $this->livestream_notifications ? 'Yes' : 'No',
            ], $this->email ?: null));
        }

        $this->reset();
        $this->sent = true;
    }
};
?>

<div class="not-prose container pt-4 pb-2 mx-auto bg-gray-100 rounded-lg">
    @php($input = 'block w-full px-4 py-3 leading-tight border rounded-sm appearance-none focus:outline-hidden focus:bg-white')
    @php($label = 'block mb-2 text-xs font-bold tracking-wide uppercase')
    <div class="mx-6 content md:mx-4">
        @if ($sent)
            <p class="mb-4 font-black text-green-900" role="status">Your notification request has been sent.</p>
        @else
            <form wire:submit="submit" class="w-full" novalidate>
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
                    <label for="dsd-first-name">Leave this empty</label>
                    <input wire:model="first_name" id="dsd-first-name" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="flex flex-wrap mb-4 -mx-3">
                    <div class="w-full px-3 mb-6 md:w-1/4 md:mb-0">
                        <label class="{{ $label }}" for="dsd-name">Name</label>
                        <input wire:model="name" class="{{ $input }}" id="dsd-name" type="text" autocomplete="name" required>
                    </div>
                    <div class="w-full px-3 md:w-1/4">
                        <label class="{{ $label }}" for="dsd-email">Email</label>
                        <input wire:model="email" class="{{ $input }}" id="dsd-email" type="email" placeholder="name@domain.com" autocomplete="email">
                    </div>
                    <div class="w-full px-3 md:w-1/4">
                        <label class="{{ $label }}" for="dsd-phone">SMS / WhatsApp</label>
                        <input wire:model="phone" class="{{ $input }}" id="dsd-phone" type="tel" placeholder="555-555-5555" autocomplete="tel">
                    </div>
                    <div class="w-full px-3 md:w-1/4">
                        <label class="{{ $label }}" for="dsd-method">Method</label>
                        <select wire:model="notification_method" class="{{ $input }}" id="dsd-method" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap mb-4 -mx-3">
                    <div class="flex w-full px-3 md:w-1/2">
                        <label class="items-center text-xs tracking-wide uppercase" for="dsd-livestream">
                            <input wire:model="livestream_notifications" id="dsd-livestream" type="checkbox" class="mr-2">
                            Subscribe to Livestream Notifications
                        </label>
                    </div>
                    <div class="w-full px-3 md:w-1/2">
                        <button class="button-primary" type="submit" wire:loading.attr="disabled">Submit</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
