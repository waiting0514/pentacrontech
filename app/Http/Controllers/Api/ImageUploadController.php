<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImageUploadController extends Controller
{
    use ApiResponse;

    public function upload(Request $request): JsonResponse
    {
        // 富文字編輯器與 Logo 上傳共用，具備任一內容編輯權限即可
        abort_unless($request->user()->canAny([
            'product.update', 'news.update', 'knowledge.update', 'banner.manage', 'system.manage',
        ]), 403);

        $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/images', $filename, 'public');
        $url = asset('storage/' . $path);

        return $this->successResponse([
            'location' => $url,
            'url' => $url,
        ]);
    }
}
