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
	private $credidentials;

	function __construct($base_url, $credidentials) {

		//set credientials if provided
		$this->credidentials = !is_null($credidentials) ? [$credidentials['username'], $credidentials['password']] : NULL;

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

		//set message body and authentication info
		$content = ['body' => $body, 'auth' => $this->credidentials];

		try {
				return $this->client->request($method, $extension, $content);
		} catch(ConnectException $e) {
				$data = [
					'response' => $e->getMessage(),
					'status_code' => 'ERR'
				];
				$this->handler->amend_last_log($data);
		} catch(BadResponseException $e) {
			//do nothing, middleware can handle this.
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
