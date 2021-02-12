<?php


namespace App\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;


class RequestBuilder
{
    private $client;
    protected $baseUrl = 'localhost:8000';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function post(string $url, array $parameters): ResponseInterface
    {
        try {
            $response = $this->client->post(
                $this->baseUrl.$url,
                [
                    'form_params' => $parameters
                ]
            );
        } catch (GuzzleException $e) {
            return $e->getResponse();
        }
        return $response;
    }

    public function put(string $url, array $body): ResponseInterface
    {
        try {
            $response = $this->client->put(
                $this->baseUrl.$url,
                [
                    'json' => $body
                ]
            );
        } catch (GuzzleException $e) {
            return $e->getResponse();
        }
        return $response;
    }

    public function delete(string $url): ResponseInterface
    {
        try {
            $response = $this->client->delete($this->baseUrl.$url);
        } catch (GuzzleException $e) {
            return $e->getResponse();
        }
        return $response;
    }

    public function get(string $url): ResponseInterface
    {
        try {
            return $this->client->get($this->baseUrl.$url);
        } catch (GuzzleException $e) {
            return $e->getResponse();
        }
    }
}