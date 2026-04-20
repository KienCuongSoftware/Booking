<x-public-layout
    :title="__('Hủy đặt phòng & hoàn tiền')"
    :description="__('Chính sách hủy, phí và hoàn tiền trên nền tảng đặt phòng.')"
>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <h1 class="text-2xl font-bold text-bcom-navy sm:text-3xl">{{ __('Hủy đặt phòng & hoàn tiền') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Cập nhật gần nhất:') }} {{ now()->translatedFormat('d/m/Y') }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-slate-700 sm:text-base">
                <p>
                    {{ __('Mỗi khách sạn có thể áp dụng chính sách hủy và mức phí riêng (theo bậc thời gian trước ngày nhận phòng). Khi bạn đặt phòng, mức phí hủy áp dụng cho đơn cụ thể được hiển thị tại bước thanh toán / xác nhận và trong chi tiết đơn.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Hủy từ phía khách') }}</h2>
                <p>
                    {{ __('Bạn có thể hủy đơn trong phần “Đơn đặt của tôi” nếu đơn còn ở trạng thái cho phép hủy. Số tiền hoàn lại (nếu có) phụ thuộc vào chính sách của khách sạn và thời điểm bạn hủy.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Hủy / thay đổi từ phía khách sạn') }}</h2>
                <p>
                    {{ __('Trong trường hợp khách sạn không thể cung cấp phòng đã đặt, chúng tôi khuyến nghị khách sạn liên hệ khách và xử lý theo quy định nội bộ và pháp luật hiện hành. Khách nên giữ biên lai thanh toán và thông tin đơn để làm việc với bộ phận hỗ trợ.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Thanh toán & hoàn tiền') }}</h2>
                <p>
                    {{ __('Thời gian ghi nhận hoàn tiền có thể khác nhau tùy phương thức thanh toán (chuyển khoản, ví, cổng thanh toán…). Mọi giao dịch đều phải tuân theo điều khoản của nhà cung cấp thanh toán và ngân hàng phát hành thẻ (nếu có).') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Liên hệ') }}</h2>
                <p>
                    {{ __('Nếu bạn cần hỗ trợ về một đơn cụ thể, vui lòng đăng nhập và sử dụng kênh tin nhắn trên đơn hoặc liên hệ quản trị viên nền tảng qua email hỗ trợ được cấu hình trên website.') }}
                </p>

                <p class="rounded-lg border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
                    {{ __('Nội dung trang này mang tính thông tin chung. Điều khoản ràng buộc pháp lý chi tiết nằm tại trang Điều khoản sử dụng và theo hợp đồng / xác nhận từng giao dịch.') }}
                </p>
            </div>
        </article>
    </div>
</x-public-layout>
