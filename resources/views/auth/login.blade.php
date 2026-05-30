<x-guest-layout heading="С возвращением" subheading="Войдите, чтобы продолжить работу с конспектами.">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Пароль')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm" style="color:var(--ink2)">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded" style="accent-color:var(--accent)">
                Запомнить меня
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm hover:underline" style="color:var(--accent)" href="{{ route('password.request') }}">
                    Забыли пароль?
                </a>
            @endif
        </div>

        <x-primary-button>Войти</x-primary-button>

        <p class="text-center text-sm pt-2" style="color:var(--muted)">
            Нет аккаунта?
            <a href="{{ route('register') }}" class="font-medium hover:underline" style="color:var(--accent)">Зарегистрироваться</a>
        </p>
    </form>
</x-guest-layout>
