<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelEmailTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $hotels = Hotel::query()
            ->where('host_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return view('host.email-templates.index', compact('hotels'));
    }

    public function edit(Request $request, Hotel $hotel): View
    {
        abort_unless($hotel->host_id === $request->user()->id, 403);

        $hotel->loadMissing('province');
        $templates = is_array($hotel->email_templates) ? $hotel->email_templates : [];

        return view('host.hotels.email-templates', compact('hotel', 'templates'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        abort_unless($hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'customer_created' => ['nullable', 'string', 'max:8000'],
            'host_created' => ['nullable', 'string', 'max:8000'],
        ]);

        $current = is_array($hotel->email_templates) ? $hotel->email_templates : [];
        foreach (['customer_created', 'host_created'] as $key) {
            $value = isset($validated[$key]) ? trim((string) $validated[$key]) : '';
            if ($value === '') {
                unset($current[$key]);
            } else {
                $current[$key] = $value;
            }
        }

        $hotel->forceFill([
            'email_templates' => $current === [] ? null : $current,
        ])->save();

        return redirect()
            ->route('host.hotels.email-templates.edit', $hotel)
            ->with('status', __('Đã lưu mẫu email.'));
    }
}
