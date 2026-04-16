<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\WaitlistEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistSlotAvailableMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public WaitlistEntry $entry,
        public Booking $releasedBooking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Có chỗ trống — :hotel', ['hotel' => $this->entry->hotel->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking.waitlist-slot',
        );
    }
}
