<?php

namespace Jiny\Mail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('admin.mail.from_address', config('mail.from.address'));
        $fromName = config('admin.mail.from_name', config('mail.from.name'));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: '[' . config('app.name') . '] 회원가입을 축하합니다',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'jiny-auth::mail.welcome',
            with: [
                'user' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
