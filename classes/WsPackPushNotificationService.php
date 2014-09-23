<?php
/**
 * Abstract Class for PushNotification Services
 *
 * @package ws_pack
 */
abstract class WsPackPushNotificationService implements WsPackPushNotificationInterface {
	
	/**
	 * Function to log data related to the push notification service. Debug purposes
	 * 
	 * @param array $content data to be logged
	 */
	abstract protected function log(array $content);
}
