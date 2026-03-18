<?php

namespace App\Services;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SmsPoolService {
	protected string $baseUrl;
	protected string $apiKey;

	public function __construct()
	{
		$this->baseUrl = rtrim(config('services.smspool.base_url'), '/');
		$this->apiKey = config('services.smspool.key');
	}

	protected function post(string $endpoint, array $data = []): Response
	{
		return Http::asForm()->post("{$this->baseUrl}/{$endpoint}", array_merge([
			'key' => $this->apiKey,
		], $data));
	}

	public function orderSms(string $country, ?string $service = null, ?string $pool = null): array
	{
		$payload = [
			'country' => $country,
			'service' => $service ?? "Instagram / Threads",
		];

		if ($pool) {
			$payload['pool'] = $pool;
		}

		$response = $this->post('purchase/sms', $payload);

		$response->throw();

		return $response->json();
	}

	public function checkSms(string $orderId): array
	{
		$response = $this->post('sms/check', [
			'orderid' => $orderId,
		]);

		$response->throw();

		return $response->json();
	}

	public function getActiveOrders(): array
	{
		$response = $this->post('request/active');

		$response->throw();

		return $response->json();
	}
}