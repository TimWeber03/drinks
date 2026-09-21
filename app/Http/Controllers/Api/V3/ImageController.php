<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V3\ImageResource;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends ApiController
{
    /**
     * Advertise that this server handles images.
     */
    public function capabilities(): Response
    {
        return response()->noContent();
    }

    public function store(Request $request): JsonResponse
    {
        $file = $request->file('image');

        if ($file === null || is_array($file) || ! $file->isValid()) {
            return response()->json(['message' => 'No image uploaded.'], 400);
        }

        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return response()->json(['message' => 'Only jpg, png and gif images are supported.'], 415);
        }

        $maxBytes = (int) config('spacemarket.max_image_kilobytes') * 1024;

        if ($file->getSize() > $maxBytes) {
            return response()->json(['message' => 'Uploaded file too large.'], 413);
        }

        $image = Image::storeUploadedFile($file, 'images');

        return response()->json([ImageResource::make($image)->resolve($request)]);
    }

    /**
     * Image metadata.
     */
    public function show(Request $request, Image $image): JsonResponse
    {
        return response()->json([ImageResource::make($image)->resolve($request)]);
    }

    /**
     * The image file itself.
     */
    public function data(Image $image): StreamedResponse|JsonResponse
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($image->path)) {
            return response()->json(['message' => 'Image file not found.'], 404);
        }

        return $disk->response($image->path);
    }
}
