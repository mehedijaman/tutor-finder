<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogImageUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BlogUploadController extends Controller
{
    public function storeImage(BlogImageUploadRequest $request): JsonResponse
    {
        $directory = 'blog/uploads/'.now()->format('Y/m');
        $path = $request->file('image')->store($directory, 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
        ]);
    }
}
