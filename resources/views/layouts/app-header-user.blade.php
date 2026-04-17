@php
    $user = auth()->user();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
    $isCustomer = $user->role->value === 'customer';
@endphp

<x-dropdown align="right" :width="'full'" contentClasses="py-1 bg-white">
    <x-slot name="trigger">
        <button
            type="button"
            class="{{ $isCustomer
                ? 'inline-flex max-w-full items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/30'
                : 'inline-flex max-w-full items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 shadow-sm hover:border-slate-300 hover:bg-sky-50/80 focus:outline-none focus:ring-2 focus:ring-bcom-blue/25' }}"
        >
            @if ($user->avatar)
                <img src="{{ $user->avatar }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover {{ $isCustomer ? 'ring-2 ring-white/30' : 'ring-2 ring-slate-200' }}" width="36" height="36" />
            @else
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white {{ $isCustomer ? 'bg-white/20 ring-2 ring-white/25' : 'bg-bcom-navy ring-2 ring-slate-200/80' }}" aria-hidden="true">{{ $initial }}</span>
            @endif
            <span class="min-w-0 truncate text-start">
                <span class="block truncate font-semibold {{ $isCustomer ? 'text-white' : 'text-bcom-navy' }}">{{ $user->name }}</span>
                <span class="block truncate text-xs font-normal {{ $isCustomer ? 'text-sky-100/90' : 'text-gray-500' }}">{{ $user->role->shortLabelVi() }}</span>
            </span>
            <svg class="h-4 w-4 shrink-0 {{ $isCustomer ? 'text-sky-100' : 'text-sky-500' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        @if ($isCustomer)
            <x-dropdown-link :href="route('profile.edit')" class="{{ request()->routeIs('profile.edit') ? 'bg-sky-50 font-medium text-bcom-navy' : '' }}">
                {{ __('Hồ sơ') }}
            </x-dropdown-link>
            <x-dropdown-link :href="route('customer.bookings.index')" class="{{ request()->routeIs('customer.bookings.index', 'customer.bookings.show', 'customer.bookings.pass', 'customer.bookings.review.*') ? 'bg-sky-50 font-medium text-bcom-navy' : '' }}">
                {{ __('Đơn đặt của tôi') }}
            </x-dropdown-link>
            <x-dropdown-link :href="route('customer.bookings.cancellable')" class="{{ request()->routeIs('customer.bookings.cancellable') ? 'bg-sky-50 font-medium text-bcom-navy' : '' }}">
                {{ __('Có thể hủy') }}
            </x-dropdown-link>
            <x-dropdown-link :href="route('customer.bookings.rebook')" class="{{ request()->routeIs('customer.bookings.rebook') ? 'bg-sky-50 font-medium text-bcom-navy' : '' }}">
                {{ __('Đặt lại') }}
            </x-dropdown-link>
            <x-dropdown-link :href="route('customer.waitlist.index')" class="{{ request()->routeIs('customer.waitlist.*') ? 'bg-sky-50 font-medium text-bcom-navy' : '' }}">
                {{ __('Danh sách chờ') }}
            </x-dropdown-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-5 py-3 text-start text-sm leading-5 text-gray-700 hover:bg-sky-50 hover:text-bcom-navy focus:bg-sky-50 focus:outline-none">
                    {{ __('Đăng xuất') }}
                </button>
            </form>
        @else
            <x-dropdown-link :href="route('profile.edit')">
                {{ __('Hồ sơ') }}
            </x-dropdown-link>
            @if ($user->role->value === 'host')
                <div class="my-1 border-t border-slate-100"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm leading-5 text-gray-700 hover:bg-sky-50 hover:text-bcom-navy focus:bg-sky-50 focus:outline-none">
                        {{ __('Đăng xuất') }}
                    </button>
                </form>
            @endif
        @endif
    </x-slot>
</x-dropdown>
