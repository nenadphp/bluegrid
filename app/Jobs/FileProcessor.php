<?php

namespace App\Jobs;

use App\Services\FileProcessors\FileProcessorService;
use App\Services\Interfaces\ClientFileDataProcessorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class FileProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ClientFileDataProcessorInterface $clientFileDataProcessor)
    {

        try {
            Log::info(sprintf('File data processor started at: %s', now()));
            $fileData = $clientFileDataProcessor->get();

            $links = [];

            foreach ($fileData['items'] as $data) {
                if (empty($data['fileUrl'])) {
                    continue;
                }

                $parsedUrl = parse_url($data['fileUrl']);
                $ipAddress = (string)$parsedUrl['host'] ?? null;
                if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                    continue;
                }

                if (!isset($links[$ipAddress])) {
                    $links[$ipAddress] = [];
                }

                $urlWithOutBaseDomain = rtrim(str_replace('http://34.8.32.234:48183/', '', $data['fileUrl']), '/');

                $pathInfo = str_replace(' ', '', pathinfo($urlWithOutBaseDomain));

                if (!($pathInfo['extension'] ?? null)) {
                    continue;
                }

                $extension = $pathInfo['extension'];

                if (!in_array($extension, ['txt', 'lck', 'tmpl', 'conf'])) {
                    continue;
                }

                $baseName = $pathInfo['basename'] ?? null;
                $dirname = $pathInfo['dirname'] ?? null;


                if (str_contains($dirname, '/')) {

                    $nestedDirName =  str_replace(' ', '', pathinfo($urlWithOutBaseDomain));

                    if (empty($nestedDirName['extension'])) {
                        continue;
                    }

                    $dirname = explode('/', $dirname);

                    $currentArray = &$links[$ipAddress];

                    foreach ($dirname as $dir) {
                        if (!isset($currentArray[$dir])) {
                            $currentArray[$dir] = [];
                        }
                        $currentArray = &$currentArray[$dir];
                    }

                    $currentArray[] = $nestedDirName['basename'];

                    continue;
                }

                if ($pathInfo['dirname'] === '.') {
                    $links[$ipAddress][] = $baseName;
                    continue;
                }

                if (!is_array($dirname)) {
                    if (!isset($links[$ipAddress][$dirname])) {
                        $links[$ipAddress][$dirname] = [];
                    }

                    $links[$ipAddress][$dirname][] = $baseName;
                }
            }

            Cache::add(FileProcessorService::FILE_DATA_PROCESSOR_CACHE_KEY,
                $links,
                FileProcessorService::FILE_DATA_PROCESSOR_CACHE_KEY_VALIDITY
            );

            Cache::forget(FileProcessorService::FILE_DATA_PROCESSOR_DATA_FETCHING_CACHE_KEY);
            Log::info(sprintf('File data processor finished at: %s', now()));
        } catch (Throwable $e) {
            Log::error(sprintf('Error with file processing data, error: %s', $e->getMessage()));
        }
    }
}
