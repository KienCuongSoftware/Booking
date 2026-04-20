<x-public-layout
    :title="__('Quyền riêng tư')"
    :description="__('Cách chúng tôi thu thập, sử dụng và bảo vệ dữ liệu cá nhân.')"
>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <h1 class="text-2xl font-bold text-bcom-navy sm:text-3xl">{{ __('Quyền riêng tư') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Cập nhật gần nhất:') }} {{ now()->translatedFormat('d/m/Y') }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-slate-700 sm:text-base">
                <p>
                    {{ __('Chúng tôi tôn trọng quyền riêng tư của bạn. Trang này mô tả các loại thông tin có thể được xử lý khi bạn sử dụng dịch vụ đặt phòng và tài khoản trên nền tảng.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Dữ liệu chúng tôi có thể thu thập') }}</h2>
                <ul class="list-disc space-y-2 pl-5">
                    <li>{{ __('Thông tin tài khoản: họ tên, email, mật khẩu (được mã hóa), ảnh đại diện nếu bạn đăng nhập bằng mạng xã hội.') }}</li>
                    <li>{{ __('Thông tin đặt phòng: ngày lưu trú, số khách, ghi chú, lịch sử trạng thái đơn, tin nhắn liên quan đơn.') }}</li>
                    <li>{{ __('Dữ liệu kỹ thuật: địa chỉ IP, loại trình duyệt, nhật ký lỗi cơ bản — nhằm vận hành và bảo mật hệ thống.') }}</li>
                </ul>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Mục đích sử dụng') }}</h2>
                <p>
                    {{ __('Dữ liệu được dùng để tạo và quản lý tài khoản, xử lý đặt phòng, gửi thông báo quan trọng (ví dụ xác minh email, cập nhật đơn), cải thiện dịch vụ, tuân thủ nghĩa vụ pháp lý và ngăn chặn gian lận.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Chia sẻ với bên thứ ba') }}</h2>
                <p>
                    {{ __('Thông tin cần thiết để hoàn tất đặt phòng có thể được chia sẻ với chủ khách sạn / nhân viên được ủy quyền. Nhà cung cấp thanh toán (nếu có) nhận dữ liệu tối thiểu để xử lý giao dịch theo chính sách của họ.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Lưu trữ & bảo mật') }}</h2>
                <p>
                    {{ __('Chúng tôi áp dụng các biện pháp kỹ thuật và tổ chức hợp lý để bảo vệ dữ liệu. Thời gian lưu trữ phụ thuộc yêu cầu vận hành, kế toán và pháp luật.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Quyền của bạn') }}</h2>
                <p>
                    {{ __('Tùy quy định tại quốc gia / khu vực của bạn, bạn có thể có quyền truy cập, chỉnh sửa, xóa hoặc hạn chế xử lý dữ liệu cá nhân. Liên hệ quản trị viên nền tảng để được hướng dẫn.') }}
                </p>

                <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    {{ __('Đây là bản mô tả chuẩn cho môi trường demo / triển khai nội bộ. Trước khi mở công khai, bạn nên nhờ luật sư rà soát theo luật bảo vệ dữ liệu áp dụng (ví dụ Nghị định / Luật liên quan tại Việt Nam hoặc GDPR nếu phục vụ khách EU).') }}
                </p>
            </div>
        </article>
    </div>
</x-public-layout>
