@props(['status' => null])

@php $message = $status ?? session('status'); @endphp

@if ($message)
    <div
        {{ $attributes->merge(['class' => 'rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-200']) }}
        data-test="status-banner"
        role="status"
    >
        {{ $message }}
    </div>
@endif
