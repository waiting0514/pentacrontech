<?php

use Illuminate\Support\Facades\App;
use App\Models\Language;

if (!function_exists('lroute')) {
    /**
     * Generate a localized URL for the application.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @return string
     */
    function lroute($name, $parameters = [], $absolute = true)
    {
        $currentLocale = App::getLocale();
        $defaultLanguage = Language::getDefault();
        $defaultCode = $defaultLanguage ? $defaultLanguage->code : config('app.locale');

        // 如果是預設語言，使用原始路由名稱 (Clean URL)
        if ($currentLocale === $defaultCode) {
            return route($name, $parameters, $absolute);
        }

        // 如果是非預設語言，嘗試使用 .localized 路由
        // 檢查該路由是否存在 (避免有些路由沒有 multilang 定義)
        if (Route::has($name . '.localized')) {
            // 自動注入 locale 參數
            $parameters['locale'] = $currentLocale;
            return route($name . '.localized', $parameters, $absolute);
        }

        // Fallback: 如果沒有 .localized 版本，就用原始路由
        return route($name, $parameters, $absolute);
    }
}
