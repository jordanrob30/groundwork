<nav class="-mx-3 flex flex-1 justify-end">
    @auth
        <a
            href="{{ url('/dashboard') }}"
            class="rounded-md px-3 py-2 text-text-primary ring-1 ring-transparent transition hover:text-brand focus:outline-none focus-visible:ring-brand"
        >
            Dashboard
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="rounded-md px-3 py-2 text-text-primary ring-1 ring-transparent transition hover:text-brand focus:outline-none focus-visible:ring-brand"
        >
            Log in
        </a>

        @if (Route::has('register'))
            <a
                href="{{ route('register') }}"
                class="rounded-md px-3 py-2 text-text-primary ring-1 ring-transparent transition hover:text-brand focus:outline-none focus-visible:ring-brand"
            >
                Register
            </a>
        @endif
    @endauth
</nav>
