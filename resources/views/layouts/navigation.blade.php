<nav x-data="{ open: false }" class="bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm shadow-slate-900/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}" class="rounded-xl p-1 -m-1 hover:bg-sky-50 transition-colors">
                        <x-application-logo class="h-9 w-9" />
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link
                        :href="route(auth()->user()->role->dashboardRouteName())"
                        :active="request()->routeIs('admin.dashboard', 'host.dashboard', 'staff.dashboard', 'customer.bookings.*', 'dashboard')"
                    >
                        {{ __('Bảng điều khiển') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl text-gray-700 bg-white border border-slate-200 hover:bg-sky-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-bcom-blue/30 transition"
                        >
                            <div class="flex flex-col items-start text-start">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="text-xs font-normal text-bcom-blue/80">{{ Auth::user()->role->shortLabelVi() }}</span>
                            </div>
                            <svg class="fill-current h-4 w-4 text-sky-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Hồ sơ') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Đăng xuất') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button
                    type="button"
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-xl text-bcom-blue hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-bcom-blue/30 transition"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-100 bg-white">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link
                :href="route(auth()->user()->role->dashboardRouteName())"
                :active="request()->routeIs('admin.dashboard', 'host.dashboard', 'staff.dashboard', 'customer.bookings.*', 'dashboard')"
            >
                {{ __('Bảng điều khiển') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-slate-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-bcom-blue/80">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Hồ sơ') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Đăng xuất') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
