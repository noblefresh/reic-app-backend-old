@component('mail::message')
# Dear {{ $user->email }},

Your OTP is {{ $otp }}.

Thank for using our service.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
