<?php

namespace Planzer\Api\Models;

class ApiConfig
{
  private const API_BASE_URL = 'https://api.planzergroup.com';
  private const OCP_APIM_SUBSCRIPTION_KEY = '71ff3aeeeb614de1b7e647e45aa33c5c';

  public function getApiBaseUrl(): string
  {
    return self::API_BASE_URL;
  }

  public function getOcpApimSubsctionKey(): string
  {
    return self::OCP_APIM_SUBSCRIPTION_KEY;
  }
}
