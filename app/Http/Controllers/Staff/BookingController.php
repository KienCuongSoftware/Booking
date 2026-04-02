<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        return view('staff.bookings.index');
    }

    public function pending(): View
    {
        return view('staff.bookings.pending');
    }

    public function history(): View
    {
        return view('staff.bookings.history');
    }
}

