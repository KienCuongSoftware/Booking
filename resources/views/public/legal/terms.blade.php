<x-public-layout
    :title="__('Điều khoản sử dụng')"
    :description="__('Điều kiện sử dụng dịch vụ đặt phòng trên nền tảng.')"
>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <h1 class="text-2xl font-bold text-bcom-navy sm:text-3xl">{{ __('Điều khoản sử dụng') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Cập nhật gần nhất:') }} {{ now()->translatedFormat('d/m/Y') }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-slate-700 sm:text-base">
                <p>
                    {{ __('Bằng việc truy cập website và tạo tài khoản, bạn đồng ý tuân thủ các điều khoản dưới đây. Nếu không đồng ý, vui lòng không sử dụng dịch vụ.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Tài khoản') }}</h2>
                <p>
                    {{ __('Bạn chịu trách nhiệm bảo mật thông tin đăng nhập và mọi hoạt động diễn ra dưới tài khoản của mình. Thông tin đăng ký phải trung thực; chúng tôi có thể khóa tài khoản nếu phát hiện vi phạm nghiêm trọng hoặc gian lận.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Đặt phòng & thanh toán') }}</h2>
                <p>
                    {{ __('Đơn đặt phòng là thỏa thuận giữa bạn và khách sạn (chủ cơ sở lưu trú), trong khuôn khổ công cụ do nền tảng cung cấp. Giá, điều kiện phòng và chính sách hủy hiển thị tại thời điểm đặt là căn cứ chính.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Hành vi cấm') }}</h2>
                <ul class="list-disc space-y-2 pl-5">
                    <li>{{ __('Sử dụng dịch vụ cho mục đích bất hợp pháp, lừa đảo hoặc quấy rối.') }}</li>
                    <li>{{ __('Can thiệp trái phép vào hệ thống, thử thách bảo mật, thu thập dữ liệu tự động trái phép.') }}</li>
                    <li>{{ __('Đăng nội dung xúc phạm, sai sự thật hoặc vi phạm quyền của bên thứ ba.') }}</li>
                </ul>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Giới hạn trách nhiệm') }}</h2>
                <p>
                    {{ __('Nền tảng hỗ trợ kết nối và quản lý đơn; chất lượng thực tế của chỗ ở do khách sạn chịu trách nhiệm trực tiếp. Trong phạm vi pháp luật cho phép, chúng tôi không chịu trách nhiệm đối với thiệt hại gián tiếp hoặc do sự kiện bất khả kháng.') }}
                </p>

                <h2 class="text-lg font-semibold text-bcom-navy">{{ __('Thay đổi điều khoản') }}</h2>
                <p>
                    {{ __('Chúng tôi có thể cập nhật điều khoản; phiên bản mới sẽ được đăng tại trang này kèm ngày hiệu lực. Việc bạn tiếp tục sử dụng dịch vụ sau thời điểm đó được hiểu là chấp nhận thay đổi (trừ khi luật bắt buộc khác).') }}
                </p>

                <p class="rounded-lg border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
                    {{ __('Bản điều khoản này phục vụ môi trường phát triển / demo. Trước khi vận hành thương mại, cần được luật sư soạn thảo / thẩm định theo pháp luật Việt Nam hoặc khu vực bạn kinh doanh.') }}
                </p>
            </div>
        </article>
    </div>
</x-public-layout>
