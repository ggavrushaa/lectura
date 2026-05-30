<x-lectura-layout title="Профиль">
<div class="max-w-2xl mx-auto anim-fade-up">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm mb-5 hover:underline" style="color:var(--muted)">← К списку лекций</a>
    <h1 class="text-3xl font-bold tracking-tight mb-7">Профиль</h1>

    <div class="space-y-5">
        <div class="card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card p-6 sm:p-8" style="border-color:color-mix(in oklab, var(--danger) 30%, var(--line))">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
</x-lectura-layout>
