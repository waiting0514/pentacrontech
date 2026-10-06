@props(['provider' => 'none', 'siteKey' => null])

@if($provider === 'recaptcha' && $siteKey)
    <div class="g-recaptcha mb-4" data-sitekey="{{ $siteKey }}"></div>
@elseif($provider === 'turnstile' && $siteKey)
    <div class="cf-turnstile mb-4" data-sitekey="{{ $siteKey }}"></div>
@endif
