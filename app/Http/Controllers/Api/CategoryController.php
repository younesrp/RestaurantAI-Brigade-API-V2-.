<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('plates')->get();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('plates');
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        
        $category->delete();
        return response()->json(null, 204);
    }

    public function plates(Category $category): JsonResponse
    {
        $plates = $category->plates()->with('ingredients')->get();
        return response()->json($plates);
    }
}
