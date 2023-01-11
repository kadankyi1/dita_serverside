@component('mail::message')

# Message
{{ $data['message_text'] }}


# User's Name
{{ $data['user_name'] }}


# User's Email
{{ $data['user_email'] }}


<br>
{{ config('app.name') }} APP
@endcomponent
