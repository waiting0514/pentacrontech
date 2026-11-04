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
        overflow: hidden;
        background-color: var(--color-navy);
        background-image:
            linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
        background-size: 48px 48px;
        color: #fff;
        padding: clamp(72px, 9vw, 120px) 0 clamp(56px, 7vw, 96px);
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
    .page-hero-scrim {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(90deg, rgba(7,26,43,.97) 0%, rgba(7,26,43,.82) 45%, rgba(7,26,43,.5) 100%);
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

<section class="page-hero ix-dark">
    @if($image)
    <img src="{{ $image }}" alt="" class="page-hero-img" data-page-hero-img loading="eager">
    <div class="page-hero-scrim"></div>
    @endif
    <div class="ix-wrap relative z-10 w-full">
        <div class="max-w-3xl flex flex-col gap-6">
            <p class="ix-eyebrow"><b>{{ \App\Models\SiteSetting::current()?->name ?? config('app.name') }}</b></p>
            <h1 class="ix-d-lg">{{ $title }}</h1>
            @if($subtitle)
            <p class="ix-body-lg ix-muted">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
</section>
