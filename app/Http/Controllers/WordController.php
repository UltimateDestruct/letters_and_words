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

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', (int) config('words.results_per_page'));

        if ($page < 1) {
            return response()->json([
                'message' => 'Page must be at least 1.',
            ], 422);
        }

        if ($perPage < 1 || $perPage > 200) {
            return response()->json([
                'message' => 'per_page must be between 1 and 200.',
            ], 422);
        }

        $payload = $words->getByLengthPaginated($length, $page, $perPage);

        return response()->json([
            'count' => $payload['count'],
            'first' => $payload['first'],
            'last' => $payload['last'],
            'words' => $payload['words'],
            'page' => $payload['page'],
            'per_page' => $payload['per_page'],
            'total_pages' => $payload['total_pages'],
        ]);
    }
}
