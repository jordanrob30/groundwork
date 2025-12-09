<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-bg-elevated border border-border-default rounded-md font-semibold text-xs text-text-primary uppercase tracking-widest shadow-sm hover:bg-bg-surface focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-base disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
