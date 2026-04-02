<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        return view('customer.bookings.index');
    }

    public function cancellable(): View
    {
        return view('customer.bookings.cancellable');
    }

    public function rebook(): View
    {
        return view('customer.bookings.rebook');
    }
}

