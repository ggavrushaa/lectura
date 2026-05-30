<section>
    <header>
        <h2 class="text-lg font-semibold">
            Смена пароля
        </h2>

        <p class="mt-1 text-sm" style="color:var(--muted)">
            Используйте длинный случайный пароль для безопасности.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Текущий пароль')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Новый пароль')" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Повторите пароль')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn btn-accent">Сохранить</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm" style="color:var(--ok)"
                >Сохранено.</p>
            @endif
        </div>
    </form>
</section>
