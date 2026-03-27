<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'dietary_tags' => $request->user()->dietary_tags,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dietary_tags' => 'array',
            'dietary_tags.*' => 'in:vegan,no_sugar,no_cholesterol,gluten_free,no_lactose',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->dietary_tags = $request->dietary_tags ?? [];
        $user->save();

        return response()->json([
            'dietary_tags' => $user->dietary_tags,
        ]);
    }
}
