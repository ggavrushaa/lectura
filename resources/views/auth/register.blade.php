<x-guest-layout heading="Создать аккаунт" subheading="Меньше минуты — и можно загружать первую лекцию.">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Имя')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Как вас зовут" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Пароль')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Повторите пароль')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button>Создать аккаунт</x-primary-button>

        <p class="text-center text-sm pt-2" style="color:var(--muted)">
            Уже есть аккаунт?
            <a href="{{ route('login') }}" class="font-medium hover:underline" style="color:var(--accent)">Войти</a>
        </p>
    </form>
</x-guest-layout>
