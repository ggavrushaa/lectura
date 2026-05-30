<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold" style="color:var(--danger)">
            Удаление аккаунта
        </h2>

        <p class="mt-1 text-sm" style="color:var(--muted)">
            После удаления аккаунта все данные и лекции будут безвозвратно удалены. Сохраните нужное заранее.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Удалить аккаунт</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold">
                Удалить аккаунт?
            </h2>

            <p class="mt-1 text-sm" style="color:var(--muted)">
                После удаления все данные и лекции будут безвозвратно удалены. Введите пароль для подтверждения.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Пароль" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="w-3/4"
                    placeholder="Пароль"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Отмена
                </x-secondary-button>

                <x-danger-button>
                    Удалить аккаунт
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
