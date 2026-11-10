<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', config('app.locale'));

        if (in_array($locale, Language::activeCodes(), true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
