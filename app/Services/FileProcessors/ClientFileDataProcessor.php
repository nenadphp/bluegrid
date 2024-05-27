<?php

namespace App\Services\FileProcessors;

use App\Services\Exceptions\ClientFileDataProcessorException;
use App\Services\Interfaces\ClientFileDataProcessorInterface;
use GuzzleHttp\Client;
use Throwable;

class ClientFileDataProcessor implements ClientFileDataProcessorInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    private const FILE_API = '/api/test';

    /**
     * @param Client $client
     * @param string $baseUrl
     */
    public function __construct(Client $client, string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return array
     * @throws ClientFileDataProcessorException
     */
    public function get(): array
    {
        try {
            $data = $this->client->get($this->baseUrl . self::FILE_API);
        } catch (Throwable $exception) {
            throw new ClientFileDataProcessorException(
                sprintf('Failed to get data from server: %s', $exception->getMessage())
            );
        }

        return json_decode( $data->getBody(), true );
    }
}
