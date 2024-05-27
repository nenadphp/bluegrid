<?php

namespace App\Services\FileProcessors;

use App\Jobs\FileProcessor;
use App\Services\Interfaces\ClientFileDataProcessorInterface;
use App\Services\Interfaces\FileProcessorServiceInterface;
use Illuminate\Support\Facades\Cache;

class FileProcessorService implements FileProcessorServiceInterface
{
    public const FILE_DATA_PROCESSOR_CACHE_KEY = 'FILE_DATA_PROCESSOR_CACHE_KEY:';
    public const FILE_DATA_PROCESSOR_DATA_FETCHING_CACHE_KEY = 'FILE_DATA_PROCESSOR_DATA_FETCHING_CACHE_KEY:';
    public const FILE_DATA_PROCESSOR_CACHE_KEY_VALIDITY = 60 * 60 * 24;


    /**
     * @var ClientFileDataProcessorInterface
     */
    private $clientFileProcessorDataFetch;

    /**
     * @param ClientFileDataProcessorInterface $clientFileProcessorDataFetch
     */
    public function __construct(ClientFileDataProcessorInterface $clientFileProcessorDataFetch)
    {
        $this->clientFileProcessorDataFetch = $clientFileProcessorDataFetch;
    }

    public function process(): bool
    {
        Cache::add(FileProcessorService::FILE_DATA_PROCESSOR_DATA_FETCHING_CACHE_KEY, true, 60);

        FileProcessor::dispatch($this->clientFileProcessorDataFetch)
            ->delay(1);

        return true;
    }
}
