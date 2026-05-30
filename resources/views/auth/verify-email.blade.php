<x-guest-layout heading="Подтвердите email" subheading="Мы отправили ссылку для подтверждения на вашу почту.">
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm rounded-lg px-3 py-2" style="color:var(--ok); background:var(--ok-soft)">
            Новая ссылка для подтверждения отправлена на вашу почту.
        </div>
    @endif

    <p class="text-sm mb-5" style="color:var(--muted)">
        Не получили письмо? Отправим ещё раз.
    </p>

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Отправить письмо повторно</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost w-full justify-center">Выйти</button>
        </form>
    </div>
</x-guest-layout>
