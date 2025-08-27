<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * List all categories with children
     */
    public function index()
    {
        return response()->json(Category::with('children')->get());
    }

    /**
     * Show single category by ID
     */
    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Store a new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'images.*' => 'sometimes|image|max:2048',
        ]);

        $imagePaths = [];

        // Only handle uploaded files if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('categories', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'images' => $imagePaths, // safe: empty array if no files
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Update an existing category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'images.*' => 'sometimes|image|max:2048',
        ]);

        $imagePaths = $category->images ?? [];

        // Replace old images if new ones uploaded
        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($imagePaths as $old) {
                $oldPath = str_replace('/storage/', '', $old);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new images
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('categories', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $category->update([
            'name' => $request->name ?? $category->name,
            'slug' => $request->name ? Str::slug($request->name) : $category->slug,
            'description' => $request->description ?? $category->description,
            'parent_id' => $request->parent_id ?? $category->parent_id,
            'images' => $imagePaths,
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Delete a category and its images
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->images) {
            foreach ($category->images as $img) {
                $path = str_replace('/storage/', '', $img);
                Storage::disk('public')->delete($path);
            }
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
