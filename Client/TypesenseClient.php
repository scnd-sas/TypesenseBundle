<?php

namespace ACSEO\TypesenseBundle\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use ACSEO\TypesenseBundle\Exception\TypesenseException;

class TypesenseClient
{
    private $host;
    private $apiKey;
    private $client;

    public function __construct(string $host, string $apiKey, HttpClientInterface $client)
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function get(string $endpoint): array
    {
        return $this->api($endpoint, [], 'GET');
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'POST');
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->api($endpoint, $data, 'DELETE');
    }

    private function api(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $response = $this->client->request($method, "http://{$this->host}/{$endpoint}", [
            'json'    => $data,
            'headers' => [
                'X-TYPESENSE-API-KEY' => $this->apiKey
            ]
        ]);
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return json_decode($response->getContent(), true);
        }
        throw new TypesenseException($response);
    }
}