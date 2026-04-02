<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('host.rooms.index');
    }
}

