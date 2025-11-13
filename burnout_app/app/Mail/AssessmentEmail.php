<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * AssessmentEmail
 * Mailable class for sending follow-up counseling appointment emails
 */
class AssessmentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     * 
     * @param array $emailData Contains student info, burnout category, appointment date, and message
     */
    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     * Sets email subject based on whether appointment is scheduled
     */
    public function envelope(): Envelope
    {
        $subject = 'Burnalytics - Burnout Assessment Follow-up';
        
        // If appointment is scheduled, update subject
        if (!empty($this->emailData['appointmentDatetime'])) {
            $subject = 'Burnalytics - Counseling Session Scheduled';
        }
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     * Uses simple email template from views folder
     */
    public function content(): Content
    {
        return new Content(
            view: 'email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
