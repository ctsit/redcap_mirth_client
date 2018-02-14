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
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;
use Monolog\Logger;


/**
 * REDCap Mirth Connect client class.
 */
class REDCapMirthClient {

	private $client;
	private $logger;

	function __construct($base_url) {
		//create a stack to store middleware
		$stack = HandlerStack::create();

		//create middleware that logs client requests and the responses it gets
		$this->logger = new Logger('database logger');
		$this->logger->pushHandler(new \MirthLogHandler($_REQUEST['pid']));

		$stack->push(
    Middleware::log(
        $this->logger,
				new MessageFormatter('{method}\\{uri}\\{code}\\{request}\\{response}')
				)
		);

		//create the actual client that sends out the API calls
		$this->client = new Client(['base_uri' => $base_url, 'handler' => $stack]);
	}

	function request($method, $extension, $body) {
		try {
				return $this->client->request($method, $extension, ['body' => $body]);
		} catch(ConnectException $e) {
				$response = $e->getMessage();
				$request = $e->getRequest();
				$method = $request->getMethod();
				$uri = $request->getUri();

				$message = "$method\\$uri\\NULL\\Unavailable\\$response";
				$this->logger->error($message);
		}
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
