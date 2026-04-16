<x-public-layout :title="$hotel->name" :description="\Illuminate\Support\Str::limit(strip_tags($hotel->description ?? ''), 160)">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif
        <nav class="mb-6 text-sm text-gray-600">
            <a href="{{ route('home') }}" class="font-medium text-bcom-blue hover:text-bcom-navy">{{ __('Khách sạn') }}</a>
            <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            <span class="text-gray-900">{{ $hotel->name }}</span>
        </nav>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
            <div class="space-y-5 p-6 sm:p-8">
                @php $gallery = $hotel->galleryImages; @endphp

                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ $hotel->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $hotel->province ? $hotel->province->type.' '.$hotel->province->name : $hotel->city }}
                        — {{ $hotel->address }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @if ($hotel->star_rating)
                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-900">{{ $hotel->star_rating }}★</span>
                    @endif
                    @if (! empty($avgRating))
                        <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-bcom-navy">{{ __('Đánh giá') }}: {{ number_format((float) $avgRating, 1) }}★</span>
                    @endif
                    @if ($hotel->old_price)
                        <p class="text-gray-500"><span class="line-through">{{ number_format((float) $hotel->old_price, 0, ',', '.') }} VND</span></p>
                    @endif
                    <p class="text-lg font-semibold text-bcom-blue">
                        {{ number_format((float) ($hotel->new_price ?? $hotel->base_price), 0, ',', '.') }} VND / {{ __('đêm') }}
                    </p>
                    <span class="text-xs text-gray-500">({{ __('Giá tham khảo — loại phòng có thể khác') }})</span>
                </div>

                @if ($hotel->amenities->isNotEmpty())
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Tiện nghi khách sạn') }}</p>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($hotel->amenities as $amenity)
                                <li class="inline-flex rounded-lg border border-slate-200 bg-sky-50/90 px-2.5 py-1 text-xs font-medium text-bcom-navy">{{ $amenity->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Hình ảnh') }}</p>
                    @if ($gallery->isEmpty())
                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-gray-100">
                            <img src="{{ $hotel->thumbnailUrl() }}" alt="" class="aspect-video w-full object-cover object-center sm:max-h-96" loading="eager" decoding="async">
                        </div>
                    @else
                        <div class="mt-3 flex flex-col gap-2 lg:flex-row lg:items-start lg:gap-2">
                            <div class="block w-full overflow-hidden rounded-xl border border-slate-200 bg-gray-100 lg:w-[61.5%] lg:shrink-0">
                                <img src="{{ $hotel->thumbnailUrl() }}" alt="" class="aspect-[4/3] w-full object-cover object-center lg:aspect-[16/10]" loading="eager" decoding="async">
                            </div>
                            <div class="flex w-full flex-col gap-2 lg:w-[38.5%]">
                                @foreach ($gallery->take(2) as $gimg)
                                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-gray-100">
                                        <img src="{{ $gimg->url() }}" alt="" class="aspect-video w-full object-cover object-center" loading="lazy" decoding="async">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if ($gallery->count() > 2)
                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($gallery->slice(2) as $gimg)
                                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-gray-100">
                                        <img src="{{ $gimg->url() }}" alt="" class="aspect-[4/3] w-full object-cover object-center" loading="lazy" decoding="async">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

                @if ($hotel->description)
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Giới thiệu') }}</p>
                        <div class="prose prose-sm mt-2 max-w-none text-gray-700 prose-p:leading-relaxed">
                            <p class="whitespace-pre-line">{{ $hotel->description }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
            <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Loại phòng') }}</h2>
                <p class="mt-1 text-xs text-amber-800">{{ __('Chọn ngày nhận/trả phòng để hệ thống chỉ cho phép ngày còn chỗ.') }}</p>
            </div>
            @if ($hotel->roomTypes->isEmpty())
                <div class="p-8 text-center text-sm text-gray-600">{{ __('Chưa có loại phòng công khai.') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                        <thead class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                            <tr>
                                <th class="px-4 py-3">{{ __('Loại chỗ ở') }}</th>
                                <th class="px-4 py-3">{{ __('Diện tích (m²)') }}</th>
                                <th class="px-4 py-3">{{ __('Số khách') }}</th>
                                <th class="px-4 py-3">{{ __('Giá / đêm') }}</th>
                                <th class="px-4 py-3">{{ __('Còn') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($hotel->roomTypes as $rt)
                                <tr class="align-top hover:bg-sky-50/40">
                                    <td class="px-4 py-4">
                                        <div class="flex gap-3">
                                            @php $firstRoomImg = $rt->images->first(); @endphp
                                            @if ($firstRoomImg)
                                                <div class="relative isolate h-16 w-[4.5rem] shrink-0 overflow-hidden rounded-md border border-slate-200 bg-gray-100">
                                                    <img src="{{ $firstRoomImg->url() }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold text-bcom-navy">{{ $rt->name }}</p>
                                                @if ($rt->bedLines->isNotEmpty())
                                                    <ul class="mt-2 space-y-1 text-xs text-gray-700">
                                                        @foreach ($rt->bedLines as $line)
                                                            <li class="flex items-start gap-1.5">
                                                                <x-icon.bed class="mt-0.5 h-3.5 w-3.5 shrink-0 text-bcom-blue" />
                                                                <span>
                                                                    @if ($line->area_name)
                                                                        <span class="font-medium">{{ $line->area_name }}:</span>
                                                                    @endif
                                                                    {{ $line->bed_summary }}
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                                @if ($rt->amenities->isNotEmpty())
                                                    <ul class="mt-2 flex flex-wrap gap-1.5 text-xs text-gray-700">
                                                        @foreach ($rt->amenities as $am)
                                                            <li class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50/80 px-2 py-0.5">
                                                                <x-icon.check class="text-emerald-600" />
                                                                {{ $am->name }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-800">
                                        @if ($rt->area_sqm !== null)
                                            {{ rtrim(rtrim(number_format((float) $rt->area_sqm, 2, ',', '.'), '0'), ',') }} m²
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex flex-wrap gap-0.5">
                                            @for ($i = 0; $i < min($rt->max_occupancy, 8); $i++)
                                                <x-icon.user class="h-4 w-4 text-sky-500" />
                                            @endfor
                                            @if ($rt->max_occupancy > 8)
                                                <span class="text-xs text-gray-600">+{{ $rt->max_occupancy - 8 }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($rt->old_price)
                                            <p class="text-xs text-gray-500 line-through">{{ number_format((float) $rt->old_price, 0, ',', '.') }} VND</p>
                                        @endif
                                        <p class="font-medium text-bcom-blue">{{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} VND</p>
                                    </td>
                                    <td class="px-4 py-4 text-gray-800">{{ $rt->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @auth
            @if (auth()->user()->role->value === 'customer')
                <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                        <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Đặt phòng ngay') }}</h2>
                        <p class="mt-1 text-xs text-gray-600">{{ __('Giá có thể thay đổi theo cuối tuần / ngày lễ / đặt sát ngày (theo cấu hình khách sạn). Thanh toán tiền mặt, chuyển khoản hoặc PayPal.') }}</p>
                    </div>
                    <form id="booking-form" method="POST" action="{{ route('customer.bookings.store', $hotel) }}" class="grid gap-4 p-6 sm:grid-cols-2">
                        @csrf
                        <div class="sm:col-span-2">
                            <x-input-label for="room_type_id" :value="__('Loại phòng')" />
                            <select id="room_type_id" name="room_type_id" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                                <option value="">{{ __('Chọn loại phòng') }}</option>
                                @foreach ($hotel->roomTypes as $rt)
                                    <option value="{{ $rt->id }}" @selected(old('room_type_id', request('room_type_id')) == $rt->id)>
                                        {{ $rt->name }} — {{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} VND
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('room_type_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="check_in_date" :value="__('Nhận phòng')" />
                            <x-text-input id="check_in_date" type="date" name="check_in_date" class="mt-1 block w-full" :value="old('check_in_date', request('check_in_date'))" :min="now()->toDateString()" required />
                            <x-input-error :messages="$errors->get('check_in_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="check_out_date" :value="__('Trả phòng')" />
                            <x-text-input id="check_out_date" type="date" name="check_out_date" class="mt-1 block w-full" :value="old('check_out_date', request('check_out_date'))" :min="now()->addDay()->toDateString()" required />
                            <x-input-error :messages="$errors->get('check_out_date')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <p id="availability_message" class="rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-xs text-sky-900">
                                {{ __('Chọn loại phòng và khoảng ngày, hệ thống sẽ kiểm tra ngày còn chỗ trước khi cho phép thanh toán.') }}
                            </p>
                        </div>

                        <div>
                            <x-input-label for="guest_count" :value="__('Số khách')" />
                            <x-text-input id="guest_count" type="number" min="1" max="10" name="guest_count" class="mt-1 block w-full" :value="old('guest_count', request('guest_count', 1))" required />
                            <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_method" :value="__('Hình thức thanh toán')" />
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>{{ __('Tiền mặt') }}</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>{{ __('Chuyển khoản') }}</option>
                                @if (config('booking.payments.paypal.enabled') && config('services.paypal.client_id') && config('services.paypal.client_secret'))
                                    <option value="paypal" @selected(old('payment_method') === 'paypal')>{{ __('PayPal') }}</option>
                                @endif
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="payment_provider" :value="__('Cổng thanh toán (khi chọn chuyển khoản)')" />
                            <select id="payment_provider" name="payment_provider" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                                <option value="momo" @selected(old('payment_provider', 'momo') === 'momo')>{{ __('MoMo') }}</option>
                                <option value="paypal" @selected(old('payment_provider') === 'paypal')>{{ __('PayPal') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_provider')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="promo_code" :value="__('Mã giảm giá (nếu có)')" />
                            <x-text-input id="promo_code" type="text" name="promo_code" class="mt-1 block w-full" :value="old('promo_code')" />
                            <x-input-error :messages="$errors->get('promo_code')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex items-start gap-2">
                            <input id="join_waitlist" type="checkbox" name="join_waitlist" value="1" class="mt-1 rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue" @checked(old('join_waitlist'))>
                            <label for="join_waitlist" class="text-sm text-gray-700">{{ __('Nếu hết chỗ, tự động thêm tôi vào danh sách chờ (email khi có slot).') }}</label>
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="payment_reference" :value="__('Mã giao dịch (nếu có)')" />
                            <x-text-input id="payment_reference" type="text" name="payment_reference" class="mt-1 block w-full" :value="old('payment_reference')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="customer_note" :value="__('Ghi chú thêm')" />
                            <textarea id="customer_note" name="customer_note" rows="3" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">{{ old('customer_note') }}</textarea>
                        </div>

                        <div class="sm:col-span-2 flex justify-end">
                            <x-primary-button>{{ __('Tiếp tục đến thanh toán') }}</x-primary-button>
                        </div>
                    </form>
                    <div class="border-t border-slate-100 px-6 py-4">
                        <p class="text-xs text-gray-600">
                            <a href="{{ route('customer.waitlist.create', $hotel) }}" class="font-semibold text-bcom-blue hover:text-bcom-navy">{{ __('Chỉ đăng ký chờ (chưa đặt phòng)') }}</a>
                            — {{ __('nhận email khi có chỗ trống.') }}
                        </p>
                    </div>
                </div>
            @endif
        @endauth

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50">
                ← {{ __('Quay lại danh sách') }}
            </a>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-bcom-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-bcom-blue/90">
                    {{ __('Đăng nhập để đặt phòng') }}
                </a>
            @endguest
        </div>
    </div>

    @auth
        @if (auth()->user()->role->value === 'customer')
            <script>
                (() => {
                    const form = document.getElementById('booking-form');
                    const roomTypeInput = document.getElementById('room_type_id');
                    const checkInInput = document.getElementById('check_in_date');
                    const checkOutInput = document.getElementById('check_out_date');
                    const paymentMethodInput = document.getElementById('payment_method');
                    const paymentProviderInput = document.getElementById('payment_provider');
                    const paymentReferenceInput = document.getElementById('payment_reference');
                    const messageNode = document.getElementById('availability_message');
                    const availabilityUrl = @json(route('customer.hotels.availability', $hotel));
                    let blockedDates = new Set();

                    const setMessage = (text, type = 'info') => {
                        if (!messageNode) return;
                        messageNode.textContent = text;
                        const cls = messageNode.classList;
                        cls.remove('border-sky-100', 'bg-sky-50', 'text-sky-900', 'border-red-200', 'bg-red-50', 'text-red-800', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                        if (type === 'error') {
                            cls.add('border-red-200', 'bg-red-50', 'text-red-800');
                        } else if (type === 'success') {
                            cls.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                        } else {
                            cls.add('border-sky-100', 'bg-sky-50', 'text-sky-900');
                        }
                    };

                    const formatViDate = (isoDate) => {
                        const [y, m, d] = isoDate.split('-');
                        return `${d}/${m}/${y}`;
                    };

                    const nextDay = (isoDate) => {
                        const date = new Date(`${isoDate}T00:00:00`);
                        date.setDate(date.getDate() + 1);
                        return date.toISOString().slice(0, 10);
                    };

                    const eachNightInRange = (checkIn, checkOut) => {
                        const result = [];
                        const cursor = new Date(`${checkIn}T00:00:00`);
                        const checkout = new Date(`${checkOut}T00:00:00`);
                        while (cursor < checkout) {
                            result.push(cursor.toISOString().slice(0, 10));
                            cursor.setDate(cursor.getDate() + 1);
                        }
                        return result;
                    };

                    const validateRangeAgainstBlocked = () => {
                        const checkIn = checkInInput.value;
                        const checkOut = checkOutInput.value;
                        if (!checkIn || !checkOut) return true;

                        const blockedNight = eachNightInRange(checkIn, checkOut).find((d) => blockedDates.has(d));
                        if (blockedNight) {
                            setMessage(`{{ __('Khoảng ngày bạn chọn bị kín phòng vào ngày') }} ${formatViDate(blockedNight)}.`, 'error');
                            return false;
                        }

                        setMessage('{{ __('Khoảng ngày còn chỗ. Bạn có thể tiếp tục thanh toán.') }}', 'success');
                        return true;
                    };

                    const syncPaymentFields = () => {
                        const method = paymentMethodInput.value;
                        const isBankTransfer = method === 'bank_transfer';

                        paymentProviderInput.disabled = !isBankTransfer;
                        paymentProviderInput.required = isBankTransfer;
                        paymentProviderInput.classList.toggle('bg-slate-100', !isBankTransfer);

                        paymentReferenceInput.disabled = !isBankTransfer;
                        paymentReferenceInput.classList.toggle('bg-slate-100', !isBankTransfer);

                        if (!isBankTransfer) {
                            paymentProviderInput.value = 'momo';
                            paymentReferenceInput.value = '';
                        }
                    };

                    const loadBlockedDates = async () => {
                        const roomTypeId = roomTypeInput.value;
                        blockedDates = new Set();
                        if (!roomTypeId) {
                            setMessage('{{ __('Chọn loại phòng để kiểm tra ngày còn chỗ.') }}');
                            return;
                        }

                        const params = new URLSearchParams({
                            room_type_id: roomTypeId,
                        });

                        if (checkInInput.value) params.set('check_in_date', checkInInput.value);
                        if (checkOutInput.value) params.set('check_out_date', checkOutInput.value);

                        try {
                            const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                                headers: {
                                    'Accept': 'application/json',
                                }
                            });
                            if (!response.ok) throw new Error('availability_error');
                            const payload = await response.json();
                            blockedDates = new Set(payload.blocked_dates || []);

                            if (checkInInput.value && blockedDates.has(checkInInput.value)) {
                                checkInInput.value = '';
                                checkOutInput.value = '';
                                setMessage('{{ __('Ngày nhận phòng bạn chọn đã kín. Vui lòng chọn ngày khác.') }}', 'error');
                                return;
                            }

                            if (checkInInput.value) {
                                checkOutInput.min = nextDay(checkInInput.value);
                            }

                            if (checkInInput.value && checkOutInput.value) {
                                validateRangeAgainstBlocked();
                                return;
                            }

                            setMessage('{{ __('Đã cập nhật tình trạng phòng. Hãy chọn khoảng ngày để tiếp tục.') }}');
                        } catch (e) {
                            setMessage('{{ __('Không thể kiểm tra lịch trống lúc này. Vui lòng thử lại.') }}', 'error');
                        }
                    };

                    roomTypeInput.addEventListener('change', loadBlockedDates);
                    paymentMethodInput.addEventListener('change', syncPaymentFields);
                    checkInInput.addEventListener('change', () => {
                        if (checkInInput.value) {
                            checkOutInput.min = nextDay(checkInInput.value);
                            if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
                                checkOutInput.value = '';
                            }
                        }
                        loadBlockedDates();
                    });
                    checkOutInput.addEventListener('change', loadBlockedDates);

                    form.addEventListener('submit', async (event) => {
                        syncPaymentFields();
                        await loadBlockedDates();
                        if (!validateRangeAgainstBlocked()) {
                            event.preventDefault();
                        }
                    });

                    syncPaymentFields();
                })();
            </script>
        @endif
    @endauth
</x-public-layout>
