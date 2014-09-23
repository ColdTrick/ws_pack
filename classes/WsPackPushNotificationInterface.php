<?php
/**
 * Interface for PushNotifications
 * 
 * @package WsPack
 */
interface WsPackPushNotificationInterface {

	/**
	 * Function to send a message as push notification
	 * 
	 * @param string $text message to be sent
	 * 
	 * @return void
	 */
	public function sendMessage($text = "");
	
}
