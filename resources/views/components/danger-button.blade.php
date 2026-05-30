<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn']) }}
    style="background:var(--danger); color:#fff">
    {{ $slot }}
</button>
