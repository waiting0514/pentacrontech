<div class="language-switcher inline-flex items-center bg-gray-100 rounded-full px-1 py-1 border border-gray-200">
    @foreach($links as $link)
        <a href="{{ $link['url'] }}"
           class="px-3 py-1 text-[10px] sm:text-xs font-semibold transition-all duration-300 rounded-full {{ $link['active'] ? 'bg-[#BE185D] text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200' }}"
           hreflang="{{ $link['code'] }}">
            {{ $link['name'] }}
        </a>
    @endforeach
</div>
