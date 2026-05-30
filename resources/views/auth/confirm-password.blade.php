<x-guest-layout heading="Подтверждение" subheading="Это защищённый раздел — подтвердите пароль для продолжения.">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Пароль')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button>Подтвердить</x-primary-button>
    </form>
</x-guest-layout>
