<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. 取得語言設定 (使用 Model 內的 Cache 機制)
        $activeLanguages = Language::getActive();
        $defaultLanguage = Language::getDefault();

        if ($activeLanguages->isEmpty() || !$defaultLanguage) {
            return $next($request);
        }

        $activeCodes = $activeLanguages->pluck('code')->all();
        $defaultCode = $defaultLanguage->code;

        // 2. 取得路由參數 locale
        $localeParam = $request->route('locale');

        if ($localeParam) {
            // Case B: 存取預設語言前綴 (例如 /zh-TW/products) -> 301 Redirect 到 Clean URL
            if ($localeParam === $defaultCode) {
                // 建構 Clean URL：使用對應的無前綴路由名稱
                // 假設命名慣例是 name.localized -> name
                $routeName = $request->route()->getName();
                if (str_ends_with($routeName, '.localized')) {
                    $cleanRouteName = substr($routeName, 0, -10); // Remove .localized
                    
                    // 轉移所有參數 (除了 locale)
                    $params = $request->route()->parameters();
                    unset($params['locale']);
                    
                    return redirect()->route($cleanRouteName, $params, 301);
                }
                
                // Fallback: 如果路由命名不符合慣例，嘗試手動處理 URL
                // 但因為我們使用了 Macro，命名應該是符合的
            }

            // Case A: 存取有效語言前綴 (例如 /en/products)
            if (in_array($localeParam, $activeCodes)) {
                App::setLocale($localeParam);
                
                // 移除參數避免干擾 Controller
                $request->route()->forgetParameter('locale');
            } 
            else {
                // Case D: 無效語言前綴
                // 理論上路由 Regex 已經擋掉了，但為了安全起見
                abort(404);
            }
        } 
        // Case C: 存取無前綴 URL (例如 /products)
        else {
            App::setLocale($defaultCode);
        }

        return $next($request);
    }
}