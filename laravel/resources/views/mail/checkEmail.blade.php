@component('mail::message')
# {{ $user -> name }}님의 인증메일 입니다.

아래의 버튼을 눌러 인증을 하세요
{{ $url }}

@component('mail::button', ['url' => $url])
인증하기
@endcomponent

감사,<br>
{{ config('app.name') }}
@endcomponent
