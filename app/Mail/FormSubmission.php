<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A site form's submission, emailed to whoever config/content.php names for
 * that form.
 */
class FormSubmission extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $fields  Label => value.
     */
    public function __construct(
        public string $form,
        public array $fields,
        public ?string $replyToAddress = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress)] : [],
            subject: config("content.forms.{$this->form}.subject"),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.form-submission');
    }
}
