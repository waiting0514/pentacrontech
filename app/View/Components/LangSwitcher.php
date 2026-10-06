<?php

namespace App\View\Components;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class LangSwitcher extends Component
{
    public $links = [];

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->generateLinks();
    }

    protected function generateLinks()
    {
        $activeLanguages = Language::getActive();
        $defaultLanguage = Language::getDefault();

        // 單語系不顯示
        if ($activeLanguages->count() <= 1) {
            return;
        }

        $currentRouteName = Route::currentRouteName();
        if (!$currentRouteName) return;

        // 判斷當前路由 Base Name
        // 如果當前是 localized 路由 (例如 products.index.localized)，移除後綴
        $baseRouteName = $currentRouteName;
        if (str_ends_with($currentRouteName, '.localized')) {
            $baseRouteName = substr($currentRouteName, 0, -10);
        }

        // 取得參數
        $currentParams = Route::current() ? Route::current()->parameters() : [];
        unset($currentParams['locale']); // 移除 locale 參數

        foreach ($activeLanguages as $lang) {
            $isDefault = ($lang->code === $defaultLanguage->code);
            $params = $currentParams;

            try {
                if ($isDefault) {
                    // 生成 Default Language URL (Clean URL)
                    // 使用 Base Name (例如 products.index)
                    $url = route($baseRouteName, $params);
                } else {
                    // 生成 Non-Default Language URL
                    // 使用 Localized Name (例如 products.index.localized)
                    $params['locale'] = $lang->code;
                    // 檢查路由是否存在 (防呆)
                    if (Route::has($baseRouteName . '.localized')) {
                        $url = route($baseRouteName . '.localized', $params);
                    } else {
                        // 如果該路由沒有 multilang 定義，fallback 到首頁
                        $url = url($lang->code);
                    }
                }
            } catch (\Exception $e) {
                // 如果生成失敗，fallback 到首頁
                $url = $isDefault ? url('/') : url($lang->code);
            }

            $this->links[] = [
                'code' => $lang->code,
                'name' => $lang->name,
                'url' => $url,
                'active' => App::getLocale() === $lang->code,
            ];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (empty($this->links)) {
            return '';
        }

        return view('components.lang-switcher');
    }
}
