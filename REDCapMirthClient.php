<?php
/**
 * @file
 * Provides REDCap Mirth Connect client class.
 */

namespace REDCapMirthClient;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/MirthLogHandler.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\BadResponseException;
use Monolog\Logger;


/**
 * REDCap Mirth Connect client class.
 */
class REDCapMirthClient {

	private $client;
	private $logger;
	private $handler;
	private $credentials;

	function __construct($base_url, $credentials) {

		//if URL is not valid set client equal to NULL
		//not using filter_var($base_url, FILTER_VALIDATE_URL) because it
		//fails on cases like "ttp://mith_connect.com...."
		if(!preg_match_all("/(http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:\/?#[\]@\$&'\(\)\*\+,;=.]+$/m", $base_url, $match)) {
			$this->client = null;
			return;
		} else {
			$base_url = $match[0][0];
		}

		//set credientials if provided
		$this->credentials = !is_null($credentials) ? [$credentials['username'], $credentials['password']] : NULL;

		//create a stack to store middleware
		$stack = HandlerStack::create();

		//create middleware that logs client requests and the responses it gets
		$this->logger = new Logger('database logger');
		$this->handler = new \MirthLogHandler($_REQUEST['pid']);
		$this->logger->pushHandler($this->handler);

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

		//if a bad URL was provided don't let requests be sent
		if (is_null($this->client)) {
			return null;
		}

		//set message body and authentication info
		$content = ['body' => $body, 'auth' => $this->credentials];

		try {
				$response = $this->client->request($method, $extension, $content);
				return $response;
		} catch(ConnectException $e) {
				$data = [
					'response' => $e->getMessage(),
					'status_code' => 'ERR'
				];
				$this->handler->amend_last_log($data);
				return null;
		} catch(BadResponseException $e) {
				//do nothing, middleware can handle this.
				return null;
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
