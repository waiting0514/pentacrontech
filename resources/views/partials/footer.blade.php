{{-- Site Footer --}}
@php
    $site = $site ?? \App\Models\Project::first();
    $socialLinks = $site?->social_links ?? [];
@endphp

<footer class="bg-gray-50 text-gray-500 mt-auto border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            {{-- Company Info --}}
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-lg font-bold text-gray-900 mb-3 tracking-tight">{{ $site?->name ?? config('app.name') }}</h3>
                @if($site?->meta_description)
                    <p class="text-gray-400 mb-5 leading-relaxed max-w-md">{{ $site->meta_description }}</p>
                @endif

                {{-- Social Links --}}
                @if(!empty($socialLinks))
                <div class="flex space-x-3 mt-4">
                    @if(!empty($socialLinks['facebook']))
                    <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-[#BE185D] hover:text-white transition">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                    </a>
                    @endif
                    @if(!empty($socialLinks['instagram']))
                    <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-[#BE185D] hover:text-white transition">
                        <span class="sr-only">Instagram</span>
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
                    </a>
                    @endif
                    @if(!empty($socialLinks['line']))
                    <a href="{{ $socialLinks['line'] }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-[#BE185D] hover:text-white transition">
                        <span class="sr-only">Line</span>
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wider">{{ __('frontend.footer.quick_links') }}</h4>
                <ul class="space-y-2.5">
                    <li><a href="{{ lroute('products.index') }}" class="text-gray-400 hover:text-[#BE185D] transition text-sm">{{ __('frontend.nav.products') }}</a></li>
                    <li><a href="{{ lroute('news.index') }}" class="text-gray-400 hover:text-[#BE185D] transition text-sm">{{ __('frontend.nav.news') }}</a></li>
                    <li><a href="{{ lroute('knowledge.index') }}" class="text-gray-400 hover:text-[#BE185D] transition text-sm">{{ __('frontend.nav.knowledge') }}</a></li>
                    <li><a href="{{ lroute('about') }}" class="text-gray-400 hover:text-[#BE185D] transition text-sm">{{ __('frontend.nav.about') }}</a></li>
                    <li><a href="{{ lroute('contact') }}" class="text-gray-400 hover:text-[#BE185D] transition text-sm">{{ __('frontend.nav.contact') }}</a></li>
                </ul>
            </div>

            {{-- Contact Info --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wider">{{ __('frontend.footer.contact_info') }}</h4>
                <ul class="space-y-3 text-sm">
                    @if($site?->contact_email)
                    <li class="flex items-center text-gray-400">
                        <svg class="h-4 w-4 mr-2.5 text-[#BE185D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $site->contact_email }}
                    </li>
                    @endif
                    @if($site?->contact_phone)
                    <li class="flex items-center text-gray-400">
                        <svg class="h-4 w-4 mr-2.5 text-[#BE185D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $site->contact_phone }}
                    </li>
                    @endif
                    @if($site?->contact_address)
                    <li class="flex items-start text-gray-400">
                        <svg class="h-4 w-4 mr-2.5 mt-0.5 text-[#BE185D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {!! $site->contact_address !!}
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="border-t border-gray-200 mt-12 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; {{ date('Y') }} {{ $site?->name ?? config('app.name') }}. {{ __('frontend.footer.all_rights_reserved') }}.</p>
        </div>
    </div>
</footer>
