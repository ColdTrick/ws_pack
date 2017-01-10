<?php

use ColdTrick\WsPack\IonicPushService;

/**
 * IonicCloud push notification service
 *
 * @package WsPack
 */
class WsPackIonicCloud extends WsPackPushNotificationService {
	
	const SERVICE_NAME = 'ionic_cloud';
	
	private $settings;
	private $plugin_settings;
	private $iconic_client;
	
	/**
	 * Class constructor
	 *
	 * @param array $settings array of settings related to ionic cloud
	 *
	 * @return void
	 */
	public function __construct($settings) {
		$this->settings = [];
		
		if (!empty($settings) && is_array($settings)) {
			$this->settings = $settings;
		}
	}
	
	/**
	 * Sends a message
	 *
	 * @param string $text         message to be sent
	 *
	 * @see WsPackPushNotificationInterface::sendMessage()
	 *
	 * @return bool
	 */
	public function sendMessage($text = "") {
		
		if (empty($text)) {
			return false;
		}
		
		$device_token = $this->getSetting('device_token');
		if (empty($device_token)) {
			return false;
		}
		
		if (!is_array($device_token)) {
			$device_token = [$device_token];
		}
		
		$client = $this->getIonicClient();
		if (empty($client)) {
			return false;
		}
		
		$notification = [
			'message' => $text,
			'android' => [
				'badge' => true,
			],
			'ios' => [
				'badge' => (int) $this->getSetting('count'),
			],
			'payload' => [
				'site_url' => elgg_get_site_url(),
			],
		];
		
		try {
			$result = $client->notify($device_token, $notification);
		} catch (\Exception $e) {
			elgg_log("WSPack IonicPush: {$e->getMessage()}", 'NOTICE');
			return false;
		}
		
		$status_code = $result->getStatusCode();
		
		return ($status_code >= 200 && $status_code < 300);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see WsPackPushNotificationService::log()
	 */
	protected function log($content) {
		// @TODO fill this
	}
	
	/**
	 * Set a setting
	 *
	 * @param string $name  the name of the setting
	 * @param mixed  $value the value of the setting
	 *
	 * @return void
	 */
	public function setSetting($name, $value) {
		$this->settings[$name] = $value;
	}
	
	/**
	 * Get a setting
	 *
	 * @param string $setting_name the name of the setting
	 *
	 * @return null|mixed
	 */
	public function getSetting($setting_name) {
		return elgg_extract($setting_name, $this->settings);
	}
	
	/**
	 * Load plugin settings related to Ionic Cloud
	 *
	 * @return void
	 */
	private function loadPluginSettings() {
		
		if (isset($this->plugin_settings)) {
			return;
		}
		
		$this->plugin_settings = [];
		
		$plugin = elgg_get_plugin_from_id('ws_pack');
		$plugin_settings = $plugin->getAllSettings();
		if (empty($plugin_settings)) {
			return;
		}
		
		foreach ($plugin_settings as $name => $value) {
			
			if (stripos($name, 'ionic_cloud_') !== 0) {
				continue;
			}
			
			$name = substr($name, strlen('ionic_cloud_'));
			$this->plugin_settings[$name] = $value;
		}
	}
	
	/**
	 * Prepare the Ionic Cloud client
	 *
	 * @return false|IonicPushService
	 */
	private function getIonicClient() {
		
		// already loaded
		if (isset($this->iconic_client)) {
			return $this->iconic_client;
		}
		
		$this->loadPluginSettings();
		
		// only load once
		$this->iconic_client = false;
		
		$api_token = elgg_extract('api_token', $this->plugin_settings);
		$api_profile = elgg_extract('profile', $this->settings);
		if (empty($api_token) || empty($api_profile)) {
			return false;
		}
		
		try {
			$client = new IonicPushService($api_profile, $api_token);
		} catch (Exception $e) {
			return false;
		}
		
		// store for further use
		$this->iconic_client = $client;
		return $client;
	}
}
