<?php

namespace App\Providers;

use App\Services\FileProcessors\ClientFileDataProcessor;
use App\Services\FileProcessors\FileProcessorService;
use App\Services\Interfaces\ClientFileDataProcessorInterface;
use App\Services\Interfaces\FileProcessorServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(FileProcessorServiceInterface::class, function (Application $app) {
            return new FileProcessorService($app->make(ClientFileDataProcessorInterface::class));
        });

        $this->app->bind(ClientFileDataProcessorInterface::class, function () {
            return new ClientFileDataProcessor(
                new Client(),
                env('FILE_PROCESSOR_REST_URL', 'rest-test-eight.vercel.app')
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
