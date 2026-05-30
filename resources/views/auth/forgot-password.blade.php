<x-guest-layout heading="Сброс пароля" subheading="Укажите email — пришлём ссылку для восстановления.">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button>Отправить ссылку</x-primary-button>

        <p class="text-center text-sm pt-2" style="color:var(--muted)">
            <a href="{{ route('login') }}" class="font-medium hover:underline" style="color:var(--accent)">← Вернуться ко входу</a>
        </p>
    </form>
</x-guest-layout>
