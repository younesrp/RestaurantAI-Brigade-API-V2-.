<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    public function index(): JsonResponse
    {
        $ingredients = Ingredient::withCount('plates')->get();
        return response()->json($ingredients);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Ingredient::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:ingredients',
            'tags' => 'array',
            'tags.*' => 'in:contains_meat,contains_sugar,contains_cholesterol,contains_gluten,contains_lactose',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ingredient = Ingredient::create($request->all());
        return response()->json($ingredient, 201);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $this->authorize('update', $ingredient);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:ingredients,name,' . $ingredient->id,
            'tags' => 'array',
            'tags.*' => 'in:contains_meat,contains_sugar,contains_cholesterol,contains_gluten,contains_lactose',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ingredient->update($request->all());
        return response()->json($ingredient);
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $this->authorize('delete', $ingredient);
        
        $ingredient->delete();
        return response()->json(null, 204);
    }
}
