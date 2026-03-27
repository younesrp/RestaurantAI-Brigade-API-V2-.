<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Jobs\AnalyzePlateRecommendation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function analyze(Request $request, Plate $plate): JsonResponse
    {
        $existingRecommendation = Recommendation::where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->first();

        if ($existingRecommendation && $existingRecommendation->status === 'processing') {
            return response()->json(['message' => 'Analysis already in progress'], 409);
        }

        $recommendation = Recommendation::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'plate_id' => $plate->id,
            ],
            [
                'score' => 0,
                'label' => 'Processing...',
                'warning_message' => null,
                'status' => 'processing',
            ]
        );

        AnalyzePlateRecommendation::dispatch($recommendation);

        return response()->json([
            'message' => 'Analysis started',
            'recommendation_id' => $recommendation->id,
        ], 202);
    }

    public function index(Request $request): JsonResponse
    {
        $recommendations = Recommendation::where('user_id', $request->user()->id)
            ->with(['plate.category', 'plate.ingredients'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($recommendations);
    }

    public function show(Request $request, Plate $plate): JsonResponse
    {
        $recommendation = Recommendation::where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->with(['plate.category', 'plate.ingredients'])
            ->first();

        if (!$recommendation) {
            return response()->json(['message' => 'Recommendation not found'], 404);
        }

        return response()->json($recommendation);
    }
}
