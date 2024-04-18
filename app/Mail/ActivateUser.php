<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ActivateUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\User // esto es para 
     */
    protected $user;
    protected $signedUrl;
    public $pathToImage = '/storage/logo.jpg';



    /**
     * Create a new message instance.
     *
     * @return void
     */


    public function __construct(User $user, $signedUrl)
    {
        $this->user = $user;
        $this->signedUrl = $signedUrl;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: __('email.activate_subject'), 
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            html: 'emails.emails_activate',
            text: 'emails.emails_activate-text',
            with: [
                'name' => $this->user->name,
                'activateUrl' => $this->signedUrl,
            ]        
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
