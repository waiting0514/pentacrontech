<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VideoController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $this->authorize('video.manage');

        return $this->successResponse(VideoResource::collection(Video::ordered()->get()));
    }

    public function store(VideoRequest $request): JsonResponse
    {
        $video = Video::create($request->videoData());

        return $this->successResponse(new VideoResource($video), 'Video created', 201);
    }

    public function show(Video $video): JsonResponse
    {
        $this->authorize('video.manage');

        return $this->successResponse(new VideoResource($video));
    }

    public function update(VideoRequest $request, Video $video): JsonResponse
    {
        $video->update($request->videoData());

        return $this->successResponse(new VideoResource($video->fresh()), 'Video updated');
    }

    public function destroy(Video $video): JsonResponse
    {
        $this->authorize('video.manage');
        $video->delete();

        return $this->successResponse(null, 'Video deleted');
    }
}
