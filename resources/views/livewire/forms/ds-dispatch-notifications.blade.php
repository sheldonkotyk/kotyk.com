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

<div class="not-prose my-8 font-sans">
    @if ($sent)
        <flux:callout icon="check-circle" color="green" heading="Notification request sent." role="status" />
    @else
        <flux:card>
            <form wire:submit="submit" class="space-y-6" novalidate>
                @error('form')
                    <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" role="alert" />
                @enderror

                <div class="hidden" aria-hidden="true">
                    <label for="dsd-first-name">Leave this empty</label>
                    <input wire:model="first_name" id="dsd-first-name" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input wire:model="name" label="Name" autocomplete="name" required />
                    <flux:select wire:model.live="notification_method" variant="listbox" label="Send updates by">
                        <flux:select.option value="email">Email</flux:select.option>
                        <flux:select.option value="sms">SMS</flux:select.option>
                        <flux:select.option value="whatsapp">WhatsApp</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="email" type="email" label="Email" placeholder="name@domain.com" autocomplete="email" />
                    <flux:input wire:model="phone" type="tel" label="SMS or WhatsApp number" placeholder="555-555-5555" autocomplete="tel" />
                </div>
                <flux:checkbox wire:model="livestream_notifications" label="Also tell me when a livestream starts" />
                <flux:button type="submit" variant="primary">Request notifications</flux:button>
            </form>
        </flux:card>
    @endif
</div>
