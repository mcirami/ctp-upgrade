<?php

namespace App\Services;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class WiseService {

	private $apiKey;
	private $baseUri;

	private $profileId;

	public function __construct() {
		$this->apiKey = env('WISE_TOKEN');
		$this->baseUri = App::environment('production') ? 'https://api.transferwise.com' : 'https://api.sandbox.transferwise.tech';
		$this->profileId = 'P21377470';
	}

	public function createRecipient($data) {
		$response = Http::withToken($this->apiKey)
		               ->post($this->baseUri . '/v1/accounts/' ,[
			               'currency' => $data['currency'],
			               "type" => "sort_code",
						   'profile' => $this->profileId,
						   'accountHolderName' => $data['name'],
			               "details" => [
								"legalType"       => $data['accountType'],
								"sortCode"        => "040075",
				                "accountNumber"   => "37778842",
				                "dateOfBirth"     => "1961-01-01"
				           ]
		               ]);

		return $response->json();
	}
}