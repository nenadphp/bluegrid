<?php

namespace App\Http\Controllers;

use App\Services\FileProcessors\FileProcessorService;
use App\Services\Interfaces\FileProcessorServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    /**
     * @param FileProcessorServiceInterface $fileProcessorService
     * @return JsonResponse
     */
    public function files(FileProcessorServiceInterface $fileProcessorService): JsonResponse
    {
        try {
            if ($cache = Cache::get(FileProcessorService::FILE_DATA_PROCESSOR_CACHE_KEY)) {
                return response()->json(['data' => $cache]);
            }

            if ( Cache::get(FileProcessorService::FILE_DATA_PROCESSOR_DATA_FETCHING_CACHE_KEY) ) {
                return response()->json(['message' => 'Processing file data, please wait....']);
            }

            $fileProcessorService->process();

            return response()->json(['message' => 'Processing file data, please wait....']);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
