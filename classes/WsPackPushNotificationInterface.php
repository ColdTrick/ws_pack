<?php
/**
 * Interface for PushNotifications
 * 
 * @package ws_pack
 */
interface WsPackPushNotificationInterface {

	/**
	 * Function to send a message as push notification
	 * 
	 * @param string $text message to be sent
	 */
	public function sendMessage($text = "");
	
}
