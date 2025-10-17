<?php

namespace Jiny\Mail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;
    public $verificationCode;

    public function __construct($user, $verificationUrl, $verificationCode = null)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
        $this->verificationCode = $verificationCode;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('admin.mail.from_address', config('mail.from.address'));
        $fromName = config('admin.mail.from_name', config('mail.from.name'));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: '[' . config('app.name') . '] 이메일 인증',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'jiny-auth::mail.verification',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'verificationCode' => $this->verificationCode,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
