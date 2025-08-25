<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    // GET /api/banners
    public function index()
    {
        return response()->json(Banner::all());
    }

    // POST /api/banners (multi-image upload)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'images.*' => 'required|image|max:2048',
            'link' => 'nullable|string',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('banners', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $banner = Banner::create([
            'title' => $request->title,
            'images' => $imagePaths,
            'link' => $request->link,
        ]);

        return response()->json($banner, 201);
    }

    // PUT /api/banners/{id} - update title/link/images
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string',
            'images.*' => 'sometimes|image|max:2048',
            'link' => 'nullable|string',
        ]);

        // Handle new images
        $imagePaths = $banner->images ?? [];
        if ($request->hasFile('images')) {
            // Optionally delete old images
            foreach ($imagePaths as $old) {
                $oldPath = str_replace('/storage/', '', $old); // convert URL to storage path
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('banners', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $banner->update([
            'title' => $request->title ?? $banner->title,
            'images' => $imagePaths,
            'link' => $request->link ?? $banner->link,
        ]);

        return response()->json($banner);
    }

    // DELETE /api/banners/{id}
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Delete images from storage
        if ($banner->images) {
            foreach ($banner->images as $img) {
                $path = str_replace('/storage/', '', $img);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }
}
