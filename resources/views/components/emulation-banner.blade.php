@props(['user'])

<div class="bg-warning text-warning-contrast">
    <div style="max-width: 1024px; margin-left: auto; margin-right: auto; padding: 0.75rem 1rem;">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span class="font-medium">
                    You are viewing as <strong>{{ $user->name }}</strong> ({{ $user->email }})
                </span>
            </div>
            <form method="POST" action="{{ route('admin.stop-impersonate') }}">
                @csrf
                <button type="submit" class="px-3 py-1 text-sm font-medium bg-warning-contrast/20 hover:bg-warning-contrast/30 rounded transition-colors">
                    Stop Emulating
                </button>
            </form>
        </div>
    </div>
</div>
