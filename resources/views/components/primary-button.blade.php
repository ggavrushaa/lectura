<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-accent w-full justify-center']) }}>
    {{ $slot }}
</button>
