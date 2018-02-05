<?php
/**
 * @file
 * Provides REDCap Mirth Connect client class.
 */

namespace REDCapMirthClient;

require 'vendor/autoload.php';
use GuzzleHttp\Client;


/**
 * REDCap Mirth Connect client class.
 */
class REDCapMirthClient {

	private $client;

	function __construct($base_url) {
		$this->client = new Client(['base_uri' => $base_url]);
	}

	function request($method, $extension, $body) {
		return $this->client->request($method, $extension, ["body" => $body]);
	}

	function get($extension) {
		return $this->client->request('GET', $extension);
	}

	function delete($extension) {
		return $this->client->request('DELETE', $extension);
	}

	function post($extension, $body) {
		return $this->client->request('POST', $extension, ["body" => $body]);
	}

	function put($extension, $body) {
		return $this->client->request('PUT', $extension, ["body" => $body]);
	}
}
