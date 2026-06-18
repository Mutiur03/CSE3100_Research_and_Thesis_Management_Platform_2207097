<x-mail::message>
# Administrator setup code

Use this one-time code to create the first administrator account for **{{ config('app.name') }}**.

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

This code expires in **{{ $expiresMinutes }} minutes**. Requesting a new code invalidates any previous code.

<x-mail::button :url="$setupUrl">
Complete setup
</x-mail::button>

If you did not request this code, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
