<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic SEO Meta Tags --}}
    @include('partials._meta')

    {{-- Preconnect to external domains --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Noto+Sans+TC:wght@400;500;700;900&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">

    {{-- Styles --}}
    @vite(['resources/css/app.css'])

    <style>[x-cloak] { display: none !important; }</style>

    {{-- Page-specific head content --}}
    @stack('head')

    {{-- JSON-LD Structured Data --}}
    @stack('schema')
</head>
<body class="font-sans antialiased bg-white text-gray-800">
    {{-- Skip to main content for accessibility --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 ind-btn px-4 py-2 rounded">
        跳至主要內容
    </a>

    {{-- Header --}}
    @include('partials.header')

    {{-- Main Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.footer')

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Global Alpine Cart Store (localStorage persistence without plugin) --}}
    <script>
        const CART_KEY = 'inquiry_cart';

        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                items: JSON.parse(localStorage.getItem(CART_KEY) || '[]'),

                _save() {
                    localStorage.setItem(CART_KEY, JSON.stringify(this.items));
                },

                add(product, qty = 1) {
                    const existing = this.items.findIndex(i => i.product_id === product.product_id);
                    if (existing >= 0) {
                        this.items[existing].quantity += qty;
                    } else {
                        this.items.push({ ...product, quantity: qty });
                    }
                    this._save();
                },

                remove(index) {
                    this.items.splice(index, 1);
                    this._save();
                },

                updateQuantity(index, qty) {
                    if (qty < 1) {
                        this.remove(index);
                    } else {
                        this.items[index].quantity = qty;
                        this._save();
                    }
                },

                totalCount() {
                    return this.items.reduce((sum, i) => sum + i.quantity, 0);
                },

                clear() {
                    this.items = [];
                    this._save();
                },
            });
        });
    </script>

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
