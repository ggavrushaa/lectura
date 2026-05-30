@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm mb-1.5']) }} style="color:var(--ink2)">
    {{ $value ?? $slot }}
</label>
