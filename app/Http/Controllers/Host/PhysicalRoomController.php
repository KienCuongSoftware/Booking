<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\PhysicalRoom;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhysicalRoomController extends Controller
{
    public function index(Request $request, RoomType $roomType): View
    {
        abort_unless($roomType->hotel->host_id === $request->user()->id, 403);

        $roomType->load(['hotel:id,name', 'physicalRooms' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return view('host.physical-rooms.index', compact('roomType'));
    }

    public function store(Request $request, RoomType $roomType): RedirectResponse
    {
        abort_unless($roomType->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        PhysicalRoom::query()->create([
            'room_type_id' => $roomType->id,
            'label' => $validated['label'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        return back()->with('status', __('Đã thêm phòng vật lý.'));
    }

    public function update(Request $request, PhysicalRoom $physicalRoom): RedirectResponse
    {
        abort_unless($physicalRoom->roomType->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $physicalRoom->forceFill([
            'label' => $validated['label'],
            'sort_order' => (int) ($validated['sort_order'] ?? $physicalRoom->sort_order),
            'is_active' => (bool) ($validated['is_active'] ?? $physicalRoom->is_active),
        ])->save();

        return back()->with('status', __('Đã cập nhật phòng vật lý.'));
    }

    public function destroy(Request $request, PhysicalRoom $physicalRoom): RedirectResponse
    {
        abort_unless($physicalRoom->roomType->hotel->host_id === $request->user()->id, 403);

        $physicalRoom->delete();

        return back()->with('status', __('Đã xóa phòng vật lý.'));
    }
}
