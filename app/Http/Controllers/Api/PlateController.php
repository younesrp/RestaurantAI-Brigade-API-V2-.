<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plates = Plate::with(['category', 'ingredients', 'recommendations' => function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        }])->get();

        $plates->each(function ($plate) use ($request) {
            $recommendation = $plate->recommendations->first();
            $plate->recommendation_score = $recommendation ? $recommendation->score : null;
            $plate->recommendation_label = $recommendation ? $recommendation->label : null;
        });

        return response()->json($plates);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Plate::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'ingredient_ids' => 'array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plate = Plate::create($request->all());
        
        if ($request->has('ingredient_ids')) {
            $plate->ingredients()->attach($request->ingredient_ids);
        }

        $plate->load(['category', 'ingredients']);
        return response()->json($plate, 201);
    }

    public function show(Request $request, Plate $plate): JsonResponse
    {
        $plate->load(['category', 'ingredients']);
        
        $recommendation = Recommendation::where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->first();
            
        $plate->recommendation = $recommendation;
        
        return response()->json($plate);
    }

    public function update(Request $request, Plate $plate): JsonResponse
    {
        $this->authorize('update', $plate);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'ingredient_ids' => 'array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plate->update($request->all());
        
        if ($request->has('ingredient_ids')) {
            $plate->ingredients()->sync($request->ingredient_ids);
        }

        $plate->load(['category', 'ingredients']);
        return response()->json($plate);
    }

    public function destroy(Plate $plate): JsonResponse
    {
        $this->authorize('delete', $plate);
        
        $plate->delete();
        return response()->json(null, 204);
    }
}
