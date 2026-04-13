<?php

namespace App\Mail;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public ?BookingStatus $fromStatus = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Cập nhật trạng thái đơn — :code', ['code' => $this->booking->booking_code]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking.status-changed',
        );
    }
}
