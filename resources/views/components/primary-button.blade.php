<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-text-inverse uppercase tracking-widest hover:bg-brand-hover focus:bg-brand-hover active:bg-brand-active focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-base transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
