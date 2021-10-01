<?php

namespace Planzer\Api\Endpoints;

use Planzer\Api\Models\ApiConfig;

class AbstractEndpoint
{
  protected ApiConfig $api_config;

  public function __construct(ApiConfig $api_config)
  {
    $this->api_config = $api_config;
  }

  protected function callEndpoint(string $endpoint, string $json_payload = '', string $method = 'GET', array $headers = []): array
  {
    if (! in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE']) || empty($endpoint)) {
      return [];
    }

    if ('/' !== substr($endpoint, 0, 1)) {
      $endpoint = '/' . $endpoint;
    }

    $default_headers = [
      'Content-Type' => 'application/json',
      'Ocp-Apim-Subscription-Key' => $this->api_config->getOcpApimSubsctionKey(),
      'Cache-Control' => 'no-cache',
    ];

    $headers = wp_parse_args($headers, $default_headers);

    $args = [
      'method' => strtoupper($method),
      'headers' => $headers,
      'body' => $json_payload,
    ];

    $response = wp_remote_request($this->api_config->getApiBaseUrl() . $endpoint, $args);

    if (is_wp_error($response)) {
      throw new \Exception('Planzer: There was some connection error while calling ' . $endpoint . '. ' . $response->get_error_message() . ':' . $response->get_error_code(), 400);
    }

    if (401 === $response['response']['code']) {
      throw new \Exception('Planzer: Unauthorized request - check api key', $response['response']['code']);
    }

    if (404 === $response['response']['code']) {
      throw new \Exception('Planzer: Endpoint not found', $response['response']['code']);
    }

    //catch all other client and server errors
    if (400 <= $response['response']['code'] && 599 >= $response['response']['code']) {
      throw new \Exception('Planzer: Error - ' . $response['response']['message'] . ' while calling ' . $endpoint, $response['response']['code']);
    }

    $response_data = json_decode($response['body'], true);
    $response_data['http_code'] = $response['response']['code'];
    $response_data['http_message'] = $response['response']['message'];

    return $response_data;
  }
}
