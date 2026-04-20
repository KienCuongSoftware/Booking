<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = route('sitemap', absolute: true);
        $body = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /host/\nDisallow: /staff/\nDisallow: /customer/\nDisallow: /dashboard\nDisallow: /profile\nSitemap: {$sitemap}\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
