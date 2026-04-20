<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function cancellationRefunds(): View
    {
        return view('public.legal.cancellation-refunds');
    }

    public function privacy(): View
    {
        return view('public.legal.privacy');
    }

    public function terms(): View
    {
        return view('public.legal.terms');
    }
}
