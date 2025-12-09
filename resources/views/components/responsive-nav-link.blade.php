@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand text-start text-base font-medium text-brand bg-brand/10 focus:outline-none focus:text-brand focus:bg-brand/20 focus:border-brand-hover transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-text-secondary hover:text-text-primary hover:bg-bg-surface hover:border-border-muted focus:outline-none focus:text-text-primary focus:bg-bg-surface focus:border-border-muted transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
