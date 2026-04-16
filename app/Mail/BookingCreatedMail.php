<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $recipientRole = 'customer',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Đơn đặt phòng mới — :code', ['code' => $this->booking->booking_code]),
        );
    }

    public function content(): Content
    {
        $intro = null;
        $templates = $this->booking->hotel?->email_templates;
        if (is_array($templates)) {
            $intro = $this->recipientRole === 'host'
                ? ($templates['host_created'] ?? null)
                : ($templates['customer_created'] ?? null);
        }

        return new Content(
            markdown: 'mail.booking.created',
            with: [
                'intro' => $intro,
            ],
        );
    }
}
