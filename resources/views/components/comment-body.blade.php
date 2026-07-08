@props([
    'body',
    'thesis',
])

{!! \App\Models\Comment::formatBody($body, $thesis) !!}
