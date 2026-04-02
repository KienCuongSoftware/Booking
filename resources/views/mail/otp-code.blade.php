<x-mail::message>
# {{ __('Xác minh tài khoản') }}

{{ __('Mã OTP của bạn là:') }}

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

{{ __('Mã có hiệu lực trong :minutes phút. Không chia sẻ mã này với bất kỳ ai.', ['minutes' => 15]) }}

**{{ $purposeLabel }}**

{{ __('Nếu bạn không yêu cầu thao tác này, hãy bỏ qua email.') }}

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
