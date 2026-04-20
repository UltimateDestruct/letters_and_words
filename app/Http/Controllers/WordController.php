<?php

namespace App\Http\Controllers;

use App\Services\WordRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function __invoke(Request $request, WordRepository $words): JsonResponse
    {
        $length = (int) $request->query('length', 0);
        $max = $words->maxLength();

        if ($length < 1 || $length > $max) {
            return response()->json([
                'message' => "Length must be between 1 and {$max}.",
            ], 422);
        }

        $payload = $words->getByLength($length);

        return response()->json([
            'count' => $payload['count'],
            'first' => $payload['first'],
            'last' => $payload['last'],
            'words' => $payload['words'],
        ]);
    }
}
