@if(\hexa_core\Models\Setting::isPackageEnabled('hexawebsystems/laravel-hexa-package-gnews'))
@if(auth()->check())

@once('news-sidebar-header')
<p class="text-xs text-gray-600 uppercase tracking-wider pt-4 pb-1 px-3">News</p>
@endonce

<a href="{{ route('gnews.index') }}"
   class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->is('raw-gnews*') || request()->is('gnews*') ? 'sidebar-active' : 'sidebar-hover' }}">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
    </svg>
    GNews
</a>

@endif
@endif
