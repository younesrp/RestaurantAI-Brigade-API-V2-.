<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plate;
use App\Models\Ingredient;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        $this->authorize('admin', User::class);

        $stats = [
            'categories' => [
                'total' => Category::count(),
                'active' => Category::where('is_active', true)->count(),
            ],
            'plates' => [
                'total' => Plate::count(),
                'available' => Plate::where('is_available', true)->count(),
            ],
            'ingredients' => [
                'total' => Ingredient::count(),
            ],
            'recommendations' => [
                'total' => Recommendation::count(),
                'processing' => Recommendation::where('status', 'processing')->count(),
                'ready' => Recommendation::where('status', 'ready')->count(),
                'average_score' => Recommendation::where('status', 'ready')->avg('score') ?? 0,
            ],
            'users' => [
                'total' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
            ],
        ];

        $categoryStats = Category::withCount('plates')
            ->orderBy('plates_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'plates_count']);

        $recentRecommendations = Recommendation::with(['user:id,name', 'plate:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'user_id', 'plate_id', 'score', 'label', 'status', 'created_at']);

        $stats['top_categories'] = $categoryStats;
        $stats['recent_recommendations'] = $recentRecommendations;

        return response()->json($stats);
    }
}
