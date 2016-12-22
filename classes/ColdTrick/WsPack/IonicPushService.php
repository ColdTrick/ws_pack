<?php

namespace ColdTrick\WsPack;

use Dmitrovskiy\IonicPush\PushProcessor;
use Dmitrovskiy\IonicPush\Exception\BadRequestException;
use Dmitrovskiy\IonicPush\Exception\PermissionDeniedException;
use Dmitrovskiy\IonicPush\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

class IonicPushService extends PushProcessor {
	
	/* @var $is_win bool */
	protected $is_win;
	
	/**
	 * {@inheritDoc}
	 * @see \Dmitrovskiy\IonicPush\PushProcessor::getNotificationBody()
	 */
	protected function getNotificationBody(array $devices, array $notification) {
		$body = parent::getNotificationBody($devices, $notification);
		$body = json_decode($body, true);
		
		$body['payload'] = [
			'site_url' => elgg_get_site_url(),
		];
		
		return json_encode($body);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Dmitrovskiy\IonicPush\PushProcessor::sendRequest()
	 */
	protected function sendRequest($headers, $body) {
		$request = new Request(
            'POST',
            $this->ionicPushEndPoint,
            $headers,
            $body
        );
        $client = new Client();

        try {
        	
            $response = $client->send($request, [RequestOptions::VERIFY => !$this->isWindows()]);
            return $response;
        } catch (ClientException $e) {
            switch ($e->getCode()) {
                case 401:
                case 403: {
                    throw new PermissionDeniedException(
                        "Permission denied sending push", $e->getCode(), $e
                    );
                }
                case 400: {
                    throw new BadRequestException(
                        "Bad request sending push", 400, $e
                    );
                }
            }
        } catch (\Exception $e) {
            throw new RequestException(
                "An error occurred when sending push request with message: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }

        return null;
	}
	
	/**
	 * Check if the system runs on a Windows OS
	 *
	 * @return bool
	 */
	protected function isWindows() {
		
		if (isset($this->is_win)) {
			return $this->is_win;
		}
		
		$this->is_win = (stripos(PHP_OS, 'win') !== false);
		
		return $this->is_win;
	}
	
	/**
	 * Send a notification to all subscribers on a channel
	 *
	 * @param array $notification
	 *
	 * @return mixed|NULL
	 */
	public function notifyAll(array $notification) {
		
		$headers = $this->getNotificationHeaders();
		
		$body = json_encode([
			'profile' => $this->getProfile(),
			'send_to_all' => true,
			'notification' => $notification,
		]);
		
		return $this->sendRequest($headers, $body);
	}
}
