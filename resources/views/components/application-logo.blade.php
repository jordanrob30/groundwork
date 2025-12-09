@props(['showText' => true])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    {{-- Foundation blocks icon - representing "groundwork" --}}
    <svg class="h-8 w-8 flex-shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        {{-- Bottom layer - solid foundation --}}
        <rect x="2" y="28" width="10" height="10" rx="2" class="fill-brand" opacity="0.4"/>
        <rect x="15" y="28" width="10" height="10" rx="2" class="fill-brand" opacity="0.6"/>
        <rect x="28" y="28" width="10" height="10" rx="2" class="fill-brand" opacity="0.4"/>

        {{-- Middle layer - building up --}}
        <rect x="8" y="15" width="10" height="10" rx="2" class="fill-brand" opacity="0.75"/>
        <rect x="22" y="15" width="10" height="10" rx="2" class="fill-brand" opacity="0.75"/>

        {{-- Top block - the result of groundwork --}}
        <rect x="15" y="2" width="10" height="10" rx="2" class="fill-brand"/>
    </svg>

    @if($showText)
        <span class="text-xl font-bold text-text-primary tracking-tight">groundwork</span>
    @endif
</div>
