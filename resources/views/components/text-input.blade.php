@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-bg-elevated border-border-default text-text-primary placeholder-text-muted focus:border-brand focus:ring-brand rounded-md shadow-sm']) }}>
