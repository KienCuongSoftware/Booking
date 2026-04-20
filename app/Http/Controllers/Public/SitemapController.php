<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            route('home', absolute: true),
            route('legal.cancellation-refunds', absolute: true),
            route('legal.privacy', absolute: true),
            route('legal.terms', absolute: true),
        ];

        $hotels = Hotel::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['slug', 'updated_at']);

        foreach ($hotels as $hotel) {
            $urls[] = route('public.hotels.show', $hotel, absolute: true);
        }

        $body = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($urls as $loc) {
            $body .= '  <url>'.PHP_EOL;
            $body .= '    <loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>'.PHP_EOL;
            $body .= '    <changefreq>weekly</changefreq>'.PHP_EOL;
            $body .= '  </url>'.PHP_EOL;
        }

        $body .= '</urlset>';

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
