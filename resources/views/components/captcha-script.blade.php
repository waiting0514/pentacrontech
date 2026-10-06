@props(['provider' => 'none', 'siteKey' => null])

@if($provider === 'recaptcha' && $siteKey)
    @push('head')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endpush
@elseif($provider === 'turnstile' && $siteKey)
    @push('head')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
