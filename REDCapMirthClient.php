<?php
/**
 * @file
 * Provides REDCap Mirth Connect client class.
 */

namespace REDCapMirthClient;

require_once 'vendor/autoload.php';
require_once 'MirthLogHandler.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;


/**
 * REDCap Mirth Connect client class.
 */
class REDCapMirthClient {

	private $client;

	function __construct($base_url) {
		//create a stack to store middleware
		$stack = HandlerStack::create();

		//create middleware that logs client requests and the responses it gets
		$logger = new Logger('database logger');
		$logger->pushHandler(new \MirthLogHandler($_REQUEST['pid']));
		$stack->push(
    Middleware::log(
        $logger,
				new MessageFormatter('{method}\\{uri}\\{code}\\{request}\\{response}')
				)
		);

		//create the actual client that sends out the API calls
		$this->client = new Client(['base_uri' => $base_url, 'handler' => $stack]);
	}

	function request($method, $extension, $body) {
		return $this->client->request($method, $extension, ['body' => $body]);
	}

	function get($extension) {
		return $this->client->request('GET', $extension);
	}

	function delete($extension) {
		return $this->client->request('DELETE', $extension);
	}

	function post($extension, $body) {
		return $this->client->request('POST', $extension, ['body' => $body]);
	}

	function put($extension, $body) {
		return $this->client->request('PUT', $extension, ['body' => $body]);
	}
}
