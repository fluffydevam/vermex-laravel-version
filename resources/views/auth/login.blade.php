@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <x-ui.input
            label="Username or Email"
            name="username"
            type="text"
            required
            autocomplete="username"
            autofocus
            :error="$errors->first('username')"
        />

        <x-ui.input
            label="Password"
            name="password"
            type="password"
            required
            autocomplete="current-password"
            :error="$errors->first('password')"
            x-bind:type="showPassword ? 'text' : 'password'"
        >
            <x-slot:trailing>
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="rounded-sm px-2 py-1 text-[11px] font-semibold text-ink-muted transition-colors duration-150 hover:text-ink focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none"
                    x-text="showPassword ? 'Hide' : 'Show'"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >Show</button>
            </x-slot:trailing>
        </x-ui.input>

        <x-ui.button variant="primary" type="submit" class="w-full">Log in</x-ui.button>
    </form>
@endsection
