<?php
/**
 * Abstract Class for PushNotification Services
 *
 * @package WsPack
 */
abstract class WsPackPushNotificationService implements WsPackPushNotificationInterface {
	
	/**
	 * Function to log data related to the push notification service. Debug purposes
	 * 
	 * @param array $content data to be logged
	 * 
	 * @return void
	 */
	abstract protected function log(array $content);
}
