@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm rounded-lg px-3 py-2']) }}
         style="color:var(--ok); background:var(--ok-soft)">
        {{ $status }}
    </div>
@endif
