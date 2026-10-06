{{-- SEO Meta Tags Partial --}}
@php
    $site = $site ?? \App\Models\Project::first();
    $siteName = $site?->name ?? config('app.name', 'Laravel');
    
    // Meta title with fallback
    $pageTitle = $metaTitle ?? ($title ?? null);
    $fullTitle = $pageTitle ? "{$pageTitle} | {$siteName}" : $siteName;
    
    // Meta description
    $pageDescription = $metaDescription ?? ($description ?? '');
    
    // Meta keywords
    $pageKeywords = $metaKeywords ?? ($keywords ?? '');
    
    // Canonical URL
    $canonicalUrl = $canonical ?? url()->current();
    
    // OG Image
    $ogImage = $ogImage ?? ($site?->logo_url ?? asset('images/og-default.jpg'));

    // OG Locale
    $localeMap = ['zh-TW' => 'zh_TW', 'zh' => 'zh_TW', 'en' => 'en_US', 'ja' => 'ja_JP'];
    $ogLocale = $localeMap[app()->getLocale()] ?? str_replace('-', '_', app()->getLocale());
@endphp

{{-- Basic Meta Tags --}}
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
@if($pageKeywords)
<meta name="keywords" content="{{ $pageKeywords }}">
@endif

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph Tags (Facebook, Line) --}}
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $pageTitle ?? $siteName }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ $ogLocale }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle ?? $siteName }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">

{{-- Additional SEO --}}
<meta name="robots" content="{{ $robots ?? 'index, follow' }}">
<meta name="author" content="{{ $siteName }}">

{{-- Favicon --}}
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#062F71">
