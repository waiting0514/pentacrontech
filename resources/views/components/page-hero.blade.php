@props([
    'title' => '',
    'subtitle' => '',
    'image' => '',
])

@once
@push('head')
<style>
    .page-hero {
        position: relative;
        min-height: 40vh;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: #0c0a09;
    }
    .page-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(12,10,9,0.25) 0%, rgba(12,10,9,0.65) 100%);
        z-index: 1;
    }
    .page-hero-img {
        position: absolute;
        top: -10%;
        bottom: -10%;
        left: 0;
        right: 0;
        width: 100%;
        height: 120%;
        object-fit: cover;
        will-change: transform;
        transition: transform 0.1s linear;
    }
</style>
@endpush
@push('scripts')
<script>
    (function () {
        const imgs = document.querySelectorAll('[data-page-hero-img]');
        if (!imgs.length || window.innerWidth <= 768) return;
        window.addEventListener('scroll', function () {
            const scrolled = window.scrollY;
            imgs.forEach(function (img) {
                const heroH = img.parentElement.offsetHeight;
                if (scrolled < heroH) {
                    img.style.transform = 'translateY(' + (scrolled * 0.25) + 'px) scale(1.05)';
                }
            });
        }, { passive: true });
    })();
</script>
@endpush
@endonce

<section class="page-hero">
    @if($image)
    <img src="{{ $image }}" alt="" class="page-hero-img" data-page-hero-img loading="eager">
    @endif
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="max-w-2xl">
            <div class="w-14 h-0.5 bg-[#BE185D] mb-6"></div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white leading-tight mb-4 tracking-tight">
                {{ $title }}
            </h1>
            @if($subtitle)
            <p class="text-lg md:text-xl text-gray-300 leading-relaxed">
                {{ $subtitle }}
            </p>
            @endif
        </div>
    </div>
</section>
