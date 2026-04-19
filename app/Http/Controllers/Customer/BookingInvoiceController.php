<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingInvoiceController extends Controller
{
    public function show(Request $request, Booking $booking): Response
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        $booking->load(['hotel', 'roomType', 'customer']);

        $pdf = Pdf::loadView('customer.bookings.invoice-pdf', ['booking' => $booking])
            ->setPaper('a4');

        $filename = 'hoa-don-'.$booking->booking_code.'.pdf';

        return $pdf->download($filename);
    }
}
