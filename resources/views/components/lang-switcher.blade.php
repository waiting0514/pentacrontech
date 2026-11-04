<div class="ix-lang" aria-label="{{ __('frontend.nav.language') }}">
    @foreach($links as $link)
        @if(!$loop->first)<span aria-hidden="true" style="color:rgba(255,255,255,.4)">/</span>@endif
        <a href="{{ $link['url'] }}" hreflang="{{ $link['code'] }}" lang="{{ $link['code'] }}" @class(['on' => $link['active']])>{{ $link['name'] }}</a>
    @endforeach
</div>
