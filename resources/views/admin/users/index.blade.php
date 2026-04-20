<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-bcom-navy">{{ __('Người dùng') }}</h2>
    </x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-4">
            <x-flash-status />
            <div id="adminUsersFilterRoot" class="space-y-4">
                <form method="GET" class="flex flex-wrap gap-2" data-ajax-filter-form data-ajax-target="#adminUsersFilterRoot">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Tìm tên / email') }}" class="rounded-xl border-gray-200 text-sm">
                    <select name="role" class="rounded-xl border-gray-200 text-sm">
                        <option value="">{{ __('Vai trò') }}</option>
                        @foreach (\App\Enums\UserRole::cases() as $r)
                            <option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->shortLabelVi() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-bcom-blue px-3 py-1.5 text-xs font-semibold text-white">{{ __('Lọc') }}</button>
                </form>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-sky-50/60 text-xs font-semibold uppercase text-bcom-navy">
                            <tr>
                                <th class="px-4 py-3">{{ __('STT') }}</th>
                                <th class="px-4 py-3">{{ __('Tên') }}</th>
                                <th class="px-4 py-3">{{ __('Email') }}</th>
                                <th class="px-4 py-3">{{ __('Vai trò') }}</th>
                                <th class="px-4 py-3">{{ __('Hoạt động') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-3">{{ $users->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3">{{ $user->role->shortLabelVi() }}</td>
                                    <td class="px-4 py-3">{{ $user->is_active ? __('Có') : __('Không') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-semibold text-bcom-blue hover:underline">{{ __('Sửa') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
