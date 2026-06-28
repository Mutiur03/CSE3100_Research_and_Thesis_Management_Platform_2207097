@props([
    'body',
    'thesis',
])

{!! app(\App\Services\CommentService::class)->formatBody($body, $thesis) !!}
