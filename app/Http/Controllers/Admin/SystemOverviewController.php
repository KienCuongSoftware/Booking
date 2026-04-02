<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SystemOverviewController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.overview');
    }
}

