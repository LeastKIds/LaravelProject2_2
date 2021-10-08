@component('mail::message')
    # {{ $user -> name }}님 이메일 인증입니다.

    이메일 인증을 합니다.
    아래의 버튼을 눌러주세요.
    {{ $url }}

    @component('mail::button', ['url' => $url ])

        버튼
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
